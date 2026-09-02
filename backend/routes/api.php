<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // LinkedIn OAuth
    Route::get('/linkedin/redirect', [AuthController::class, 'linkedinRedirect']);
    Route::get('/linkedin/callback', [AuthController::class, 'linkedinCallback']);
});

// Protected Endpoints (Requires Sanctum Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Session & Profile Me
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Profile & Settings
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
    });

    // Job Applications Management
    Route::prefix('job-applications')->group(function () {
        Route::get('/stats', [JobApplicationController::class, 'stats']);
        Route::get('/export', [JobApplicationController::class, 'export']);
        Route::get('/', [JobApplicationController::class, 'index']);
        Route::post('/', [JobApplicationController::class, 'store']);
        Route::get('/{id}', [JobApplicationController::class, 'show']);
        Route::put('/{id}', [JobApplicationController::class, 'update']);
        Route::patch('/{id}/status', [JobApplicationController::class, 'updateStatus']);
        Route::delete('/{id}', [JobApplicationController::class, 'destroy']);
    });

    // Admin Restricted Endpoints
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::patch('/users/{id}/role', [AdminController::class, 'updateUserRole']);
        Route::get('/applications', [AdminController::class, 'applications']);
    });
});
