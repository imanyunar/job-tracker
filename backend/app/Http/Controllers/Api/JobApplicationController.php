<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobApplicationRequest;
use App\Models\JobApplication;
use App\Services\JobUrlParserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobApplicationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of job applications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $status = $request->query('status');
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'applied_date');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 15);

        $query = JobApplication::query()
            ->where('user_id', $userId)
            ->filterStatus($status)
            ->search($search)
            ->sortBy($sortBy, $sortOrder);

        if ($perPage === 'all' || $perPage === '-1') {
            $data = $query->get();
            return $this->successResponse($data, 'Semua data lamaran berhasil diambil.');
        }

        $paginated = $query->paginate(is_numeric($perPage) ? (int)$perPage : 15);
        return $this->paginatedResponse($paginated, 'Daftar lamaran berhasil dimuat.');
    }

    /**
     * Store a newly created job application for the authenticated user.
     */
    public function store(JobApplicationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        $jobApplication = JobApplication::create($validated);

        return $this->successResponse($jobApplication, 'Lamaran baru berhasil ditambahkan.', 201);
    }

    /**
     * Display the specified job application.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $jobApplication = JobApplication::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$jobApplication) {
            return $this->errorResponse('Data lamaran tidak ditemukan.', 404);
        }

        return $this->successResponse($jobApplication, 'Detail lamaran berhasil dimuat.');
    }

    /**
     * Update the specified job application.
     */
    public function update(JobApplicationRequest $request, int $id): JsonResponse
    {
        $jobApplication = JobApplication::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$jobApplication) {
            return $this->errorResponse('Data lamaran tidak ditemukan.', 404);
        }

        $jobApplication->update($request->validated());

        return $this->successResponse($jobApplication, 'Perubahan data lamaran berhasil disimpan.');
    }

    /**
     * Quick update for application status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:applied,screening,interview,offer,rejected,accepted'],
        ], [
            'status.required' => 'Status lamaran wajib dipilih.',
            'status.in' => 'Status pilihan tidak valid.',
        ]);

        $jobApplication = JobApplication::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$jobApplication) {
            return $this->errorResponse('Data lamaran tidak ditemukan.', 404);
        }

        $jobApplication->status = $request->input('status');
        $jobApplication->save();

        return $this->successResponse($jobApplication, "Status berhasil diubah menjadi '{$jobApplication->status}'.");
    }

    /**
     * Remove the specified job application.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $jobApplication = JobApplication::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$jobApplication) {
            return $this->errorResponse('Data lamaran tidak ditemukan.', 404);
        }

        $jobApplication->delete();

        return $this->successResponse(null, 'Data lamaran berhasil dihapus.');
    }

    /**
     * Get aggregate statistics for the authenticated user.
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $total = JobApplication::where('user_id', $userId)->count();
        $byStatus = JobApplication::where('user_id', $userId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = ['applied', 'screening', 'interview', 'offer', 'rejected', 'accepted'];
        $statusCounts = [];
        foreach ($statuses as $s) {
            $statusCounts[$s] = $byStatus[$s] ?? 0;
        }

        $active = ($statusCounts['applied'] ?? 0) + ($statusCounts['screening'] ?? 0) + ($statusCounts['interview'] ?? 0);
        $positiveRate = $total > 0 ? round((($statusCounts['interview'] + $statusCounts['offer'] + $statusCounts['accepted']) / $total) * 100, 1) : 0;

        return $this->successResponse([
            'total' => $total,
            'active_in_process' => $active,
            'positive_rate_percent' => $positiveRate,
            'by_status' => $statusCounts,
        ], 'Statistik lamaran berhasil dimuat.');
    }

    /**
     * Export applications to CSV for the authenticated user.
     */
    public function export(Request $request): StreamedResponse
    {
        $userId = $request->user()->id;
        $applications = JobApplication::where('user_id', $userId)
            ->orderBy('applied_date', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="job_applications_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($applications) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'ID',
                'Perusahaan',
                'Posisi',
                'Status',
                'Tanggal Lamar',
                'Sumber',
                'Lokasi',
                'Tautan Loker',
                'Gaji Min',
                'Gaji Max',
                'Catatan',
            ]);

            foreach ($applications as $app) {
                fputcsv($file, [
                    $app->id,
                    $app->company_name,
                    $app->position,
                    $app->status,
                    $app->applied_date?->format('Y-m-d') ?? '',
                    $app->source ?? '',
                    $app->location ?? '',
                    $app->job_url ?? '',
                    $app->salary_range_min ?? '',
                    $app->salary_range_max ?? '',
                    $app->notes ?? '',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Parse a job posting URL and extract structured job details.
     */
    public function parseUrl(Request $request, JobUrlParserService $parserService): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string', 'max:1000'],
        ], [
            'url.required' => 'Tautan lowongan wajib diisi.',
        ]);

        $url = $request->input('url');
        $extracted = $parserService->parse($url);

        return $this->successResponse($extracted, 'Data lowongan berhasil dianalisis.');
    }
}
