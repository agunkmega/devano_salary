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
use App\Http\Controllers\Api\UserDeviceApiController;
use App\Http\Controllers\MobileReleaseController;
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
    // Device Security
    Route::get('/user/devices', [UserDeviceApiController::class, 'index']);
    Route::post('/user/devices/unbind', [UserDeviceApiController::class, 'unbind']);
    Route::post('/user/devices/logout-all', [UserDeviceApiController::class, 'logoutAll']);

    Route::get('/me', [AuthController::class, 'profile']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/password', [ProfileVaultApiController::class, 'updatePassword']); 
    
    // Profile Vault & Emergency Contacts
    Route::post('/profile/update', [ProfileVaultApiController::class, 'updateProfile']);
    Route::get('/profile/documents', [ProfileVaultApiController::class, 'getDocuments']);
    Route::get('/profile/emergency-contacts', [ProfileVaultApiController::class, 'getEmergencyContacts']);
    Route::post('/profile/emergency-contacts', [ProfileVaultApiController::class, 'addEmergencyContact']);
    Route::post('/profile/emergency-contacts/{id}/delete', [ProfileVaultApiController::class, 'deleteEmergencyContact']);

    // Attendances (Mobile API)
    Route::get('/attendance/config', [AttendanceApiController::class, 'config']);
    Route::get('/attendances', [AttendanceApiController::class, 'index']);
    Route::post('/attendance/clock-in', [AttendanceApiController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceApiController::class, 'clockOut']);
    Route::post('/attendance/break-out', [AttendanceApiController::class, 'breakOut']);
    Route::post('/attendance/break-in', [AttendanceApiController::class, 'breakIn']);

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

    // Leave Approvals (Department Head)
    Route::get('/leave-approvals', [LeaveApiController::class, 'approvals']);
    Route::post('/leave-approvals/{id}/approve', [LeaveApiController::class, 'approve']);
    Route::post('/leave-approvals/{id}/reject', [LeaveApiController::class, 'reject']);

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

    // Personal Transactions (Wallet Sync)
    Route::get('/personal-transactions', [\App\Http\Controllers\Api\PersonalTransactionApiController::class, 'index']);
    Route::post('/personal-transactions', [\App\Http\Controllers\Api\PersonalTransactionApiController::class, 'store']);
    Route::delete('/personal-transactions/{id}', [\App\Http\Controllers\Api\PersonalTransactionApiController::class, 'destroy']);
    Route::post('/personal-transactions/sync', [\App\Http\Controllers\Api\PersonalTransactionApiController::class, 'sync']);

    // Personal Schedules (Calendar Sync)
    Route::get('/personal-schedules', [\App\Http\Controllers\Api\PersonalScheduleApiController::class, 'index']);
    Route::post('/personal-schedules', [\App\Http\Controllers\Api\PersonalScheduleApiController::class, 'store']);
    Route::put('/personal-schedules/{id}', [\App\Http\Controllers\Api\PersonalScheduleApiController::class, 'update']);
    Route::delete('/personal-schedules/{id}', [\App\Http\Controllers\Api\PersonalScheduleApiController::class, 'destroy']);
    Route::post('/personal-schedules/sync', [\App\Http\Controllers\Api\PersonalScheduleApiController::class, 'sync']);
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

// ── Route Streaming Media Gambar (Bebas CORS untuk Flutter Web & Mobile) ──
Route::get('/media/{path}', function ($path) {
    // 1. Cari file di beberapa kemungkinan direktori storage Laravel
    $candidates = [
        storage_path('app/public/' . $path),
        storage_path('app/' . $path),
        public_path('storage/' . $path),
        public_path($path),
    ];
    $targetFile = null;
    foreach ($candidates as $candidate) {
        if (File::exists($candidate) && !File::isDirectory($candidate)) {
            $targetFile = $candidate;
            break;
        }
    }
    // Jika file benar-benar tidak ada di server
    if (!$targetFile) {
        abort(404, 'File media tidak ditemukan: ' . $path);
    }
    // 2. Ambil mime type otomatis (image/jpeg, image/png, dsb)
    $mimeType = File::mimeType($targetFile) ?? 'application/octet-stream';
    // 3. Return file dengan header CORS lengkap
    return response()->file($targetFile, [
        'Content-Type'                => $mimeType,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods'=> 'GET, HEAD, OPTIONS',
        'Access-Control-Allow-Headers'=> '*',
        'Cache-Control'               => 'public, max-age=86400',
    ]);
})->where('path', '.*'); // <-- PENTING: agar subfolder terbaca lengkap!

// ── Mobile App Distribution & Release History ──
Route::prefix('mobile')->group(function () {
    Route::get('/latest-release', [MobileReleaseController::class, 'getLatest']);
    Route::get('/releases', [MobileReleaseController::class, 'getHistory']);
    Route::post('/upload-apk', [App\Http\Controllers\Api\MobileReleaseApiController::class, 'uploadApk']);
    Route::post('/upload-apk-chunk', [App\Http\Controllers\Api\MobileReleaseApiController::class, 'uploadChunk']);
});

Route::middleware('auth:sanctum')->post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    $socketId = $request->input('socket_id');
    $channelName = $request->input('channel_name');

    if (!$socketId || !$channelName) {
        return response()->json(['message' => 'socket_id and channel_name are required.'], 422);
    }

    $appKey = config('broadcasting.connections.reverb.key') ?? env('REVERB_APP_KEY', 'employee-key');
    $appSecret = config('broadcasting.connections.reverb.secret') ?? env('REVERB_APP_SECRET', 'employee-secret');

    if (str_starts_with($channelName, 'presence-')) {
        $channelData = json_encode([
            'user_id' => (string) $user->id,
            'user_info' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar ?? null,
                'role' => $user->role ?? null,
            ],
        ]);
        $stringToSign = "$socketId:$channelName:$channelData";
        $signature = hash_hmac('sha256', $stringToSign, $appSecret);
        return response()->json([
            'auth' => "$appKey:$signature",
            'channel_data' => $channelData,
        ]);
    } else {
        $stringToSign = "$socketId:$channelName";
        $signature = hash_hmac('sha256', $stringToSign, $appSecret);
        return response()->json([
            'auth' => "$appKey:$signature",
        ]);
    }
});




