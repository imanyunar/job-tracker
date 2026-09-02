<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
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
});
