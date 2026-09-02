<?php

use App\Http\Controllers\Api\JobApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Job Application Routes
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
