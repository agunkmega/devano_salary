<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class UserDeviceApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $currentDeviceId = $request->header('X-Device-Id') ?? $request->input('device_id');

        $devices = UserDevice::where('user_id', $user->id)
            ->orderBy('last_active_at', 'desc')
            ->get();

        $data = $devices->map(function ($d) use ($currentDeviceId) {
            return [
                'id' => (string) $d->id,
                'device_name' => $d->device_name,
                'os_version' => $d->os_version ?? 'Mobile OS',
                'device_id' => $d->device_id,
                'last_active' => $d->last_active_at ? $d->last_active_at->toIso8601String() : $d->updated_at->toIso8601String(),
                'is_current' => $currentDeviceId ? ($d->device_id === $currentDeviceId) : false,
                'location' => $d->location ?? 'Indonesia',
                'device_type' => $d->device_type ?? 'mobile',
            ];
        });

        // If list is empty, return current device info dynamically
        if ($data->isEmpty()) {
            $data = collect([
                [
                    'id' => '1',
                    'device_name' => $request->userAgent() ? (str_contains($request->userAgent(), 'Android') ? 'Android Device' : 'Web Browser') : 'Perangkat Pegawai',
                    'os_version' => 'Mobile',
                    'device_id' => $currentDeviceId ?? 'DEV-CURRENT-001',
                    'last_active' => now()->toIso8601String(),
                    'is_current' => true,
                    'location' => 'Indonesia',
                    'device_type' => 'mobile',
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function unbind(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
        ]);

        $user = $request->user();
        UserDevice::where('user_id', $user->id)
            ->where('device_id', $validated['device_id'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perangkat berhasil diputus.',
        ]);
    }

    public function logoutAll(Request $request)
    {
        $user = $request->user();
        $currentDeviceId = $request->header('X-Device-Id') ?? $request->input('device_id');

        if ($currentDeviceId) {
            UserDevice::where('user_id', $user->id)
                ->where('device_id', '!=', $currentDeviceId)
                ->delete();
        } else {
            UserDevice::where('user_id', $user->id)->delete();
        }

        // Revoke all tokens except current token
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $user->tokens()->where('id', '!=', $currentToken->id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari semua perangkat lain.',
        ]);
    }
}