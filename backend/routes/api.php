<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobApplicationController;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Endpoints (Requires Sanctum Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Profile & Logout
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
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
});
