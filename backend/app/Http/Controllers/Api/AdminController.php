<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    use ApiResponse;

    /**
     * Get global system metrics and stats for admin overview.
     */
    public function stats(): JsonResponse
    {
        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalApplications = JobApplication::count();

        $byStatus = JobApplication::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = ['applied', 'screening', 'interview', 'offer', 'rejected', 'accepted'];
        $statusCounts = [];
        foreach ($statuses as $s) {
            $statusCounts[$s] = $byStatus[$s] ?? 0;
        }

        $activeInProcess = ($statusCounts['applied'] ?? 0) + ($statusCounts['screening'] ?? 0) + ($statusCounts['interview'] ?? 0);
        $positiveRate = $totalApplications > 0
            ? round((($statusCounts['interview'] + $statusCounts['offer'] + $statusCounts['accepted']) / $totalApplications) * 100, 1)
            : 0;

        return $this->successResponse([
            'total_users' => $totalUsers,
            'total_admins' => $totalAdmins,
            'total_applications' => $totalApplications,
            'active_in_process' => $activeInProcess,
            'global_positive_rate' => $positiveRate,
            'by_status' => $statusCounts,
        ], 'Statistik sistem berhasil dimuat.');
    }

    /**
     * Get paginated users list with application counts and filters.
     */
    public function users(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $perPage = $request->query('per_page', 15);

        $query = User::withCount('jobApplications')
            ->when($role && $role !== 'all', function ($q) use ($role) {
                $q->where('role', $role);
            })
            ->when($search, function ($q) use ($search) {
                $term = '%' . strtolower($search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                });
            })
            ->orderBy('created_at', 'desc');

        $users = $query->paginate(is_numeric($perPage) ? (int)$perPage : 15);

        return $this->paginatedResponse($users, 'Daftar pengguna berhasil dimuat.');
    }

    /**
     * Update user role (promote to admin or demote to user).
     */
    public function updateUserRole(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'in:user,admin'],
        ], [
            'role.in' => 'Pilihan role tidak valid.',
        ]);

        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Pengguna tidak ditemukan.', 404);
        }

        // Prevent admin from demoting themselves
        if ($user->id === $request->user()->id && $request->input('role') !== 'admin') {
            return $this->errorResponse('Anda tidak dapat mencabut hak akses admin dari akun Anda sendiri.', 400);
        }

        $user->role = $request->input('role');
        $user->save();

        return $this->successResponse($user, "Role pengguna {$user->name} berhasil diubah menjadi '{$user->role}'.");
    }

    /**
     * Global job applications monitor.
     */
    public function applications(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = $request->query('per_page', 15);

        $query = JobApplication::with('user:id,name,email')
            ->filterStatus($status)
            ->search($search)
            ->orderBy('applied_date', 'desc');

        $applications = $query->paginate(is_numeric($perPage) ? (int)$perPage : 15);

        return $this->paginatedResponse($applications, 'Data lamaran global berhasil dimuat.');
    }
}
