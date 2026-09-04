<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Services\EmailJobStatusParserService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class EmailSyncController extends Controller
{
    public function __construct(
        protected EmailJobStatusParserService $parserService
    ) {}

    /**
     * Standard success response.
     */
    protected function successResponse(mixed $data = null, string $message = 'Sukses', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Standard error response.
     */
    protected function errorResponse(string $message = 'Terjadi kesalahan', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Parse raw email content and match against user's job applications.
     */
    public function parseEmail(Request $request): JsonResponse
    {
        $request->validate([
            'content' => ['required', 'string', 'min:5'],
            'subject' => ['nullable', 'string', 'max:500'],
            'sender' => ['nullable', 'string', 'max:500'],
            'application_id' => ['nullable', 'integer'],
        ], [
            'content.required' => 'Isi email atau cuplikan pesan wajib diisi.',
            'content.min' => 'Isi email terlalu pendek untuk dianalisis.',
        ]);

        $user = $request->user();
        $content = $request->input('content');
        $subject = $request->input('subject');
        $sender = $request->input('sender');
        $appId = $request->input('application_id');

        $result = $this->parserService->parseEmail(
            content: $content,
            subject: $subject,
            sender: $sender,
            user: $user,
            explicitApplicationId: $appId ? (int)$appId : null
        );

        return $this->successResponse($result, 'Email berhasil dianalisis.');
    }

    /**
     * Apply parsed status and notes update to the job application.
     */
    public function applyUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => ['required', 'integer'],
            'status' => ['required', 'string', 'in:applied,screening,interview,offer,rejected,accepted'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'append_note' => ['nullable', 'boolean'],
        ], [
            'application_id.required' => 'ID lamaran kerja wajib dipilih.',
            'status.required' => 'Status baru wajib ditentukan.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $user = $request->user();
        $appId = $request->input('application_id');
        $newStatus = $request->input('status');
        $noteContent = $request->input('notes');
        $appendNote = $request->boolean('append_note', true);

        /** @var JobApplication|null $application */
        $application = $user->jobApplications()->find($appId);

        if (!$application) {
            return $this->errorResponse('Lamaran pekerjaan tidak ditemukan dalam akun Anda.', 404);
        }

        $oldStatus = $application->status;
        $application->status = $newStatus;

        if (!empty($noteContent)) {
            if ($appendNote && !empty($application->notes)) {
                $application->notes = trim($application->notes) . "\n\n" . trim($noteContent);
            } else {
                $application->notes = trim($noteContent);
            }
        }

        $application->save();

        return $this->successResponse([
            'application' => $application->fresh(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ], "Status lamaran {$application->company_name} berhasil diperbarui ke " . $this->parserService->getStatusLabel($newStatus) . '.');
    }

    /**
     * Get Gmail connection status for the authenticated user.
     */
    public function getGmailStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $hasClientConfig = !empty(config('services.google.client_id')) && !empty(config('services.google.client_secret'));

        $isConnected = $user->hasGoogleConnected();
        $isExpired = false;

        if ($user->google_token_expires_at) {
            $isExpired = Carbon::now()->isAfter($user->google_token_expires_at);
        }

        return $this->successResponse([
            'is_connected' => $isConnected,
            'is_token_expired' => $isExpired,
            'google_email' => $user->google_email ?? $user->email,
            'last_synced_at' => $user->last_gmail_synced_at?->format('Y-m-d H:i:s'),
            'has_client_config' => $hasClientConfig,
        ], 'Status integrasi Gmail.');
    }

    /**
     * Redirect to Google OAuth for Gmail readonly permission.
     */
    public function googleRedirect(Request $request): RedirectResponse|JsonResponse
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', env('APP_URL', 'https://job-tracker.anythingforlove.my.id')), '/');

        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            if (!$request->expectsJson()) {
                return redirect()->to($frontendUrl . '?gmail_error=' . urlencode('Kredensial Google OAuth belum dikonfigurasi di .env backend (GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET).'));
            }
            return $this->errorResponse(
                'Kredensial Google OAuth belum dikonfigurasi di .env (GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET).',
                400
            );
        }

        try {
            // Determine user id from query token or auth session
            $userId = null;
            $token = $request->query('token') ?? $request->bearerToken();
            if ($token) {
                $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($pat) {
                    $userId = $pat->tokenable_id;
                }
            }
            if (!$userId && $request->user()) {
                $userId = $request->user()->id;
            }

            $statePayload = base64_encode(json_encode([
                'user_id' => $userId,
                'timestamp' => time(),
            ]));

            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google')
                ->stateless()
                ->scopes([
                    'openid',
                    'profile',
                    'email',
                    'https://www.googleapis.com/auth/gmail.readonly',
                ])
                ->with([
                    'access_type' => 'offline',
                    'prompt' => 'consent select_account',
                    'state' => $statePayload,
                ]);

            if (app()->environment('local')) {
                $driver->setHttpClient(new Client(['verify' => false]));
            }

            return $driver->redirect();
        } catch (Throwable $e) {
            Log::error('Google Redirect Error: ' . $e->getMessage());
            if (!$request->expectsJson()) {
                return redirect()->to($frontendUrl . '?gmail_error=' . urlencode('Gagal menghubungi server Google: ' . $e->getMessage()));
            }
            return $this->errorResponse('Gagal mengarahkan ke halaman otentikasi Google: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle OAuth callback from Google.
     */
    public function googleCallback(Request $request): RedirectResponse
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', env('APP_URL', 'https://job-tracker.anythingforlove.my.id')), '/');

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google')->stateless();

            if (app()->environment('local')) {
                $driver->setHttpClient(new Client(['verify' => false]));
            }

            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = $driver->user();

            $token = $googleUser->token;
            $refreshToken = $googleUser->refreshToken;
            $expiresIn = $googleUser->expiresIn ?? 3600;
            $expiresAt = Carbon::now()->addSeconds($expiresIn);

            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();

            // Extract target user ID from state if provided
            $user = null;
            $rawState = $request->input('state');
            if ($rawState) {
                $decoded = json_decode(base64_decode($rawState), true);
                if (!empty($decoded['user_id'])) {
                    $user = \App\Models\User::find($decoded['user_id']);
                }
            }

            // Fallback: search by existing google_id or email
            if (!$user) {
                if ($request->user()) {
                    $user = $request->user();
                } else {
                    $user = \App\Models\User::where('google_id', $googleId)
                        ->orWhere('email', $email)
                        ->first();
                }
            }

            if ($user) {
                $user->google_id = $googleId;
                $user->google_email = $email;
                $user->google_access_token = $token;
                if (!empty($refreshToken)) {
                    $user->google_refresh_token = $refreshToken;
                }
                $user->google_token_expires_at = $expiresAt;
                $user->save();

                return redirect()->to($frontendUrl . '?gmail_connected=1&email=' . urlencode($email));
            }

            return redirect()->to($frontendUrl . '?gmail_error=' . urlencode('Akun Job Tracker tidak ditemukan untuk dihubungkan.'));
        } catch (Throwable $e) {
            Log::error('Google Callback Error: ' . $e->getMessage());
            return redirect()->to($frontendUrl . '?gmail_error=' . urlencode('Otentikasi Google gagal: ' . $e->getMessage()));
        }
    }

    /**
     * Disconnect Google account.
     */
    public function disconnectGmail(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->google_access_token = null;
        $user->google_refresh_token = null;
        $user->google_token_expires_at = null;
        $user->save();

        return $this->successResponse(null, 'Koneksi akun Gmail berhasil diputuskan.');
    }

    /**
     * Scan user's Gmail inbox for recent job recruitment emails.
     */
    public function scanGmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasGoogleConnected()) {
            return $this->errorResponse('Akun Gmail belum terhubung. Silakan hubungkan akun Google terlebih dahulu.', 400);
        }

        // Check if token needs refresh
        $token = $this->ensureValidAccessToken($user);
        if (!$token) {
            return $this->errorResponse('Sesi akun Google telah kedaluwarsa. Silakan hubungkan ulang akun Google Anda.', 401);
        }

        try {
            $client = new Client([
                'verify' => !app()->environment('local'),
                'timeout' => 15,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);

            // Query relevant recruitment messages newer than 30 days
            $query = 'subject:(interview OR wawancara OR offering OR lamaran OR "seleksi" OR lolos OR assessment OR psikotes OR regret OR "belum dapat") newer_than:30d';
            $listUrl = 'https://gmail.googleapis.com/gmail/v1/users/me/messages?q=' . urlencode($query) . '&maxResults=10';

            $response = $client->get($listUrl);
            $listData = json_decode((string)$response->getBody(), true);
            $messages = $listData['messages'] ?? [];

            $parsedResults = [];

            foreach ($messages as $msgItem) {
                $msgId = $msgItem['id'];
                $msgUrl = "https://gmail.googleapis.com/gmail/v1/users/me/messages/{$msgId}?format=full";
                $msgResp = $client->get($msgUrl);
                $msgData = json_decode((string)$msgResp->getBody(), true);

                $headers = $msgData['payload']['headers'] ?? [];
                $subject = '';
                $sender = '';
                $dateStr = '';

                foreach ($headers as $h) {
                    $nameLower = strtolower($h['name']);
                    if ($nameLower === 'subject') {
                        $subject = $h['value'];
                    } elseif ($nameLower === 'from') {
                        $sender = $h['value'];
                    } elseif ($nameLower === 'date') {
                        $dateStr = $h['value'];
                    }
                }

                $body = $this->extractGmailMessageBody($msgData['payload'] ?? []);
                if (empty($body)) {
                    $body = $msgData['snippet'] ?? '';
                }

                $analysis = $this->parserService->parseEmail(
                    content: $body,
                    subject: $subject,
                    sender: $sender,
                    user: $user
                );

                $parsedResults[] = [
                    'id' => $msgId,
                    'subject' => $subject,
                    'sender' => $sender,
                    'date' => $dateStr,
                    'analysis' => $analysis,
                ];
            }

            $user->last_gmail_synced_at = Carbon::now();
            $user->save();

            return $this->successResponse([
                'scanned_count' => count($messages),
                'results' => $parsedResults,
                'last_synced_at' => $user->last_gmail_synced_at->format('Y-m-d H:i:s'),
            ], 'Inbox Gmail berhasil dipindai.');
        } catch (Throwable $e) {
            Log::error('Gmail scan error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memindai Gmail: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Ensure access token is valid, refresh if expired and refresh token is available.
     */
    protected function ensureValidAccessToken(\App\Models\User $user): ?string
    {
        $token = $user->google_access_token;
        if (empty($token)) {
            return null;
        }

        $isExpired = false;
        if ($user->google_token_expires_at) {
            $isExpired = Carbon::now()->addMinutes(2)->isAfter($user->google_token_expires_at);
        }

        if (!$isExpired) {
            return $token;
        }

        // Try refresh if refresh token exists
        if (!empty($user->google_refresh_token)) {
            try {
                $client = new Client(['verify' => !app()->environment('local')]);
                $resp = $client->post('https://oauth2.googleapis.com/token', [
                    'form_params' => [
                        'client_id' => config('services.google.client_id'),
                        'client_secret' => config('services.google.client_secret'),
                        'refresh_token' => $user->google_refresh_token,
                        'grant_type' => 'refresh_token',
                    ],
                ]);

                $data = json_decode((string)$resp->getBody(), true);
                if (!empty($data['access_token'])) {
                    $user->google_access_token = $data['access_token'];
                    $expiresIn = $data['expires_in'] ?? 3600;
                    $user->google_token_expires_at = Carbon::now()->addSeconds($expiresIn);
                    $user->save();

                    return $user->google_access_token;
                }
            } catch (Throwable $e) {
                Log::error('Google refresh token failed: ' . $e->getMessage());
            }
        }

        return $token; // Fallback to current token
    }

    /**
     * Recursively extract message body from Gmail payload parts.
     */
    protected function extractGmailMessageBody(array $payload): string
    {
        if (!empty($payload['body']['data'])) {
            return base64_decode(strtr($payload['body']['data'], '-_', '+/'));
        }

        if (!empty($payload['parts'])) {
            foreach ($payload['parts'] as $part) {
                $mime = $part['mimeType'] ?? '';
                if ($mime === 'text/plain' && !empty($part['body']['data'])) {
                    return base64_decode(strtr($part['body']['data'], '-_', '+/'));
                }
            }

            foreach ($payload['parts'] as $part) {
                $mime = $part['mimeType'] ?? '';
                if ($mime === 'text/html' && !empty($part['body']['data'])) {
                    return base64_decode(strtr($part['body']['data'], '-_', '+/'));
                }
            }

            // Recurse into subparts
            foreach ($payload['parts'] as $part) {
                $text = $this->extractGmailMessageBody($part);
                if (!empty($text)) {
                    return $text;
                }
            }
        }

        return '';
    }
}
