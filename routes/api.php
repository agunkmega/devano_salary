<?php

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashAdvanceApiController;
use App\Http\Controllers\Api\ChatApiController;
use App\Http\Controllers\Api\FingerspotWebhookController;
use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ProfileVaultApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('attendance')->name('api.attendance.')->group(function () {
    Route::post('/push', [AttendanceApiController::class, 'push'])->name('push');
    Route::get('/logs', [AttendanceApiController::class, 'logs'])->name('logs');
    Route::post('/import', [AttendanceApiController::class, 'import'])->name('import');
});

Route::match(['get', 'post'], '/fingerspot/webhook', [FingerspotWebhookController::class, 'handle']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/activate', [AuthController::class, 'activate']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/profile/update', [ProfileVaultApiController::class, 'updateProfile']);
    Route::post('/auth/password', [ProfileVaultApiController::class, 'updatePassword']);

    // Attendances (Mobile API)
    Route::get('/attendance/config', [AttendanceApiController::class, 'config']);
    Route::get('/attendances', [AttendanceApiController::class, 'index']);
    Route::post('/attendance/clock-in', [AttendanceApiController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceApiController::class, 'clockOut']);

    // Payrolls
    Route::get('/payrolls', [PayrollController::class, 'index']);
    Route::get('/payrolls/latest', [PayrollController::class, 'latest']);
    Route::get('/payrolls/{id}', [PayrollController::class, 'show']);

    // Cash Advances / Kasbon
    Route::get('/cash-advances', [CashAdvanceApiController::class, 'index']);
    Route::post('/cash-advances', [CashAdvanceApiController::class, 'store']);

    // Leaves
    Route::get('/leaves', [LeaveApiController::class, 'index']);
    Route::get('/leave-quota', [LeaveApiController::class, 'quota']);
    Route::post('/leaves', [LeaveApiController::class, 'store']);
    Route::post('/leaves/{id}/cancel', [LeaveApiController::class, 'cancel']);
    Route::get('/leave-types', [LeaveApiController::class, 'leaveTypes']);

    // Profile Vault & Emergency Contacts
    Route::get('/user/documents', [ProfileVaultApiController::class, 'getDocuments']);
    Route::get('/user/emergency-contacts', [ProfileVaultApiController::class, 'getEmergencyContacts']);
    Route::post('/user/emergency-contacts', [ProfileVaultApiController::class, 'addEmergencyContact']);
    Route::post('/user/emergency-contacts/{id}/delete', [ProfileVaultApiController::class, 'deleteEmergencyContact']);


    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationApiController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationApiController::class, 'destroy']);

    // Home & Announcements
    Route::get('/home/announcements', [HomeApiController::class, 'getAnnouncements']);
    Route::get('/home/celebrations', [HomeApiController::class, 'getCelebrations']);
    Route::post('/home/celebrations/wish', [HomeApiController::class, 'sendWish']);

    // Directory & Chat
    Route::get('/chat/config', [ChatApiController::class, 'getConfig']);
    Route::get('/chat/rooms', [ChatApiController::class, 'getRooms']);
    Route::get('/chat/directory', [ChatApiController::class, 'getDirectory']);
    Route::get('/chat/rooms/{id}/messages', [ChatApiController::class, 'getMessages']);
    Route::post('/chat/rooms/{id}/send', [ChatApiController::class, 'sendMessage']);
});

// Portal fallback alias
Route::middleware('auth:sanctum')->post('/portal/password', [ProfileVaultApiController::class, 'updatePassword']);
Route::middleware('auth:sanctum')->post('/portal/cash-advance', [CashAdvanceApiController::class, 'store']);
// Mobile App Release & Distribution
Route::get('/mobile/latest-release', [App\Http\Controllers\Api\MobileReleaseApiController::class, 'getLatest']);
Route::post('/mobile/upload-apk', [App\Http\Controllers\Api\MobileReleaseApiController::class, 'uploadApk']);

// Global CORS Preflight Handler for API
Route::options('/{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN, Application')
        ->header('Access-Control-Max-Age', '86400');
})->where('any', '.*');


// ── Mobile App Distribution & Release History ──
use App\Http\Controllers\MobileReleaseController;
Route::prefix('mobile')->group(function () {
    Route::get('/latest-release', [MobileReleaseController::class, 'getLatest']);
    Route::get('/releases', [MobileReleaseController::class, 'getHistory']);
    Route::post('/upload-apk', [App\Http\Controllers\Api\MobileReleaseApiController::class, 'uploadApk']);
    Route::post('/upload-apk-chunk', [App\Http\Controllers\Api\MobileReleaseApiController::class, 'uploadChunk']);
});
