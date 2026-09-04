<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailSyncController;
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

    // Google / Gmail OAuth Redirect & Callback
    Route::get('/google/redirect', [EmailSyncController::class, 'googleRedirect']);
    Route::get('/google/callback', [EmailSyncController::class, 'googleCallback']);
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
        Route::post('/parse-url', [JobApplicationController::class, 'parseUrl']);
        Route::get('/', [JobApplicationController::class, 'index']);
        Route::post('/', [JobApplicationController::class, 'store']);
        Route::get('/{id}', [JobApplicationController::class, 'show']);
        Route::put('/{id}', [JobApplicationController::class, 'update']);
        Route::patch('/{id}/status', [JobApplicationController::class, 'updateStatus']);
        Route::delete('/{id}', [JobApplicationController::class, 'destroy']);
    });

    // Email Scraping & Status Sync
    Route::prefix('email-sync')->group(function () {
        Route::post('/parse', [EmailSyncController::class, 'parseEmail']);
        Route::post('/apply', [EmailSyncController::class, 'applyUpdate']);
        Route::post('/create-application', [EmailSyncController::class, 'createApplication']);
        Route::get('/gmail/status', [EmailSyncController::class, 'getGmailStatus']);
        Route::get('/gmail/redirect', [EmailSyncController::class, 'googleRedirect']);
        Route::post('/gmail/disconnect', [EmailSyncController::class, 'disconnectGmail']);
        Route::post('/gmail/scan', [EmailSyncController::class, 'scanGmail']);
    });

    // Admin Restricted Endpoints
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::patch('/users/{id}/role', [AdminController::class, 'updateUserRole']);
        Route::get('/applications', [AdminController::class, 'applications']);
    });
});
