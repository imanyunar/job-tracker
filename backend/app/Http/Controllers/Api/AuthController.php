<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $isFirstUser = User::count() === 0;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $isFirstUser ? 'admin' : 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Pendaftaran akun berhasil! Selamat datang di Job Tracker.', 201);
    }

    /**
     * Log in an existing user.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !$user->password || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Email atau kata sandi yang kamu masukkan salah.', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Berhasil masuk ke akun.');
    }

    /**
     * Redirect to LinkedIn OAuth Authorization page.
     */
    public function linkedinRedirect(): RedirectResponse|JsonResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('linkedin-openid')
                ->stateless()
                ->scopes(['openid', 'profile', 'email']);

            if (app()->environment('local')) {
                $driver->setHttpClient(new Client([
                    'verify' => false,
                ]));
            }

            return $driver->redirect();
        } catch (Throwable $e) {
            Log::error('LinkedIn Redirect Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $isDebug = config('app.debug', false);
            return $this->errorResponse(
                $isDebug ? 'Gagal menginisiasi login LinkedIn: ' . $e->getMessage() : 'Gagal menghubungi server LinkedIn. Silakan coba beberapa saat lagi.',
                500
            );
        }
    }

    /**
     * Handle OAuth Callback from LinkedIn.
     */
    public function linkedinCallback(): RedirectResponse
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', env('APP_URL', 'https://job-tracker.anythingforlove.my.id')), '/');

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('linkedin-openid')
                ->stateless();

            // Disable SSL verify on local environment to bypass Windows cURL CA certificate limitation
            if (app()->environment('local')) {
                $driver->setHttpClient(new Client([
                    'verify' => false,
                ]));
            }

            /** @var \Laravel\Socialite\Two\User $linkedinUser */
            $linkedinUser = $driver->user();

            $email = $linkedinUser->getEmail();
            $name = $linkedinUser->getName() ?: ($linkedinUser->getNickname() ?: 'LinkedIn User');
            $linkedinId = $linkedinUser->getId();
            $avatar = $linkedinUser->getAvatar();

            // Find existing user by linkedin_id or by email
            $user = User::where('linkedin_id', $linkedinId)->first();

            if (!$user && $email) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                // Update missing LinkedIn fields if needed
                $user->update([
                    'linkedin_id' => $linkedinId,
                    'avatar' => $avatar ?: $user->avatar,
                ]);
            } else {
                $isFirstUser = User::count() === 0;

                // Create brand new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email ?: "linkedin_{$linkedinId}@jobtracker.local",
                    'linkedin_id' => $linkedinId,
                    'avatar' => $avatar,
                    'role' => $isFirstUser ? 'admin' : 'user',
                    'password' => null, // OAuth users don't require initial password
                ]);
            }

            // Generate Bearer token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect back to frontend with token parameter
            return redirect("{$frontendUrl}/?token=" . urlencode($token));
        } catch (Throwable $e) {
            Log::error('LinkedIn Callback OAuth Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $isDebug = config('app.debug', false);
            $userFacingMessage = $isDebug
                ? 'Gagal login dengan LinkedIn: ' . $e->getMessage()
                : 'Gagal melakukan login dengan LinkedIn. Silakan coba beberapa saat lagi.';
            return redirect("{$frontendUrl}/?error=" . urlencode($userFacingMessage));
        }
    }

    /**
     * Log out the authenticated user (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Berhasil keluar dari akun.');
    }

    /**
     * Get the authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Data pengguna berhasil diambil.');
    }
}
