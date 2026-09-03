<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get the authenticated user's full profile and statistics.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $applicationsCount = $user->jobApplications()->count();

        return $this->successResponse([
            'user' => $user,
            'stats' => [
                'total_applications' => $applicationsCount,
                'has_linkedin' => !empty($user->linkedin_id),
                'member_since' => $user->created_at?->format('Y-m-d'),
            ],
        ], 'Profil berhasil dimuat.');
    }

    /**
     * Update profile and career preferences.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'target_salary_min' => ['nullable', 'numeric', 'min:0'],
            'target_salary_max' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $min = $request->input('target_salary_min');
                    if ($value !== null && $min !== null && (float)$value < (float)$min) {
                        $fail('Target gaji maksimum harus lebih besar atau sama dengan target minimum.');
                    }
                },
            ],
            'preferred_location' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'target_salary_min.numeric' => 'Target gaji minimum harus berupa angka.',
            'target_salary_max.numeric' => 'Target gaji maksimum harus berupa angka.',
        ]);

        $user->update($validated);

        return $this->successResponse($user->fresh(), 'Profil dan preferensi karir berhasil diperbarui.');
    }

    /**
     * Change user account password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        // If user registered with email+password, verify current_password
        if ($user->password) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ], [
                'current_password.required' => 'Kata sandi saat ini wajib diisi.',
                'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
                'password.required' => 'Kata sandi baru wajib diisi.',
                'password.min' => 'Kata sandi baru minimal 6 karakter.',
                'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            ]);
        } else {
            // OAuth user creating password for the first time
            $request->validate([
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ], [
                'password.required' => 'Kata sandi baru wajib diisi.',
                'password.min' => 'Kata sandi baru minimal 6 karakter.',
                'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            ]);
        }

        $user->password = $request->input('password');
        $user->save();

        return $this->successResponse(null, 'Kata sandi berhasil diperbarui.');
    }
}
