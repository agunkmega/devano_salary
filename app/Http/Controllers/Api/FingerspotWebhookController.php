<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\FingerSpotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FingerspotWebhookController extends Controller
{
    public function __construct(protected FingerSpotService $fingerSpot) {}

    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            return response()->json(['status' => 'ok', 'message' => 'Webhook endpoint is active']);
        }

        Log::info('Fingerspot webhook received', $request->all());

        $payload = $request->all();

        if (($payload['type'] ?? null) === 'get_userinfo') {
            return $this->handleGetUserInfo($payload);
        }

        if (($payload['type'] ?? null) !== 'attlog') {
            return response()->json(['status' => 'ignored', 'reason' => 'unexpected_type']);
        }

        $cloudId = $payload['cloud_id'] ?? null;
        if ($cloudId) {
            cache()->put('fingerspot_cloud_id', $cloudId, now()->addDays(30));
            Setting::updateOrCreate(
                ['key' => 'fingerspot_cloud_id'],
                ['value' => $cloudId, 'group' => 'fingerspot']
            );
        }

        $records = $this->fingerSpot->extractRecords($payload);

        if (empty($records)) {
            Log::warning('Fingerspot webhook missing pin or scan', $payload);

            return response()->json(['status' => 'ignored', 'reason' => 'missing_data']);
        }

        $results = [];
        foreach ($records as $rec) {
            $results[] = $this->fingerSpot->processScan($rec);
        }

        return response()->json(['status' => 'ok', 'results' => $results]);
    }

    protected function handleGetUserInfo(array $payload): JsonResponse
    {
        $pin = $payload['pin'] ?? $payload['data']['pin'] ?? null;

        if (! $pin) {
            return response()->json(['status' => 'error', 'reason' => 'missing_pin']);
        }

        $employee = Employee::where('nik', $pin)->first();

        if (! $employee) {
            return response()->json(['status' => 'error', 'reason' => 'employee_not_found']);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'pin' => $employee->nik,
                'name' => $employee->full_name,
            ],
        ]);
    }
}
