<?php

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\FingerspotWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('attendance')->name('api.attendance.')->group(function () {
    Route::post('/push', [AttendanceApiController::class, 'push'])->name('push');
    Route::get('/logs', [AttendanceApiController::class, 'logs'])->name('logs');
    Route::post('/import', [AttendanceApiController::class, 'import'])->name('import');
});

Route::match(['get', 'post'], '/fingerspot/webhook', [FingerspotWebhookController::class, 'handle']);
