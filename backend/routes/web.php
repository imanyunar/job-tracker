<?php

use Illuminate\Support\Facades\Route;

// Serve Vue 3 SPA frontend if built into public/index.html, otherwise serve API info
Route::get('/{any?}', function () {
    $indexPath = public_path('index.html');
    if (file_exists($indexPath)) {
        return response()->file($indexPath);
    }

    return response()->json([
        'app' => 'Job Application Tracker API',
        'status' => 'online',
        'version' => '1.0.0',
        'endpoints' => [
            'applications' => '/api/job-applications',
            'stats' => '/api/job-applications/stats',
            'export' => '/api/job-applications/export',
        ],
    ]);
})->where('any', '^(?!api).*$');

