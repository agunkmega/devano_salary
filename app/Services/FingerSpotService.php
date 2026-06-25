<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class FingerSpotService
{
    protected string $baseUrl;
    protected string $token;
    protected ?string $deviceId;

    public function __construct()
    {
        $settings = Setting::whereIn('key', ['fingerspot_api_url', 'fingerspot_api_token', 'fingerspot_device_id'])
            ->get()
            ->keyBy('key');

        $this->baseUrl = rtrim($settings->get('fingerspot_api_url')?->value ?? 'https://api.fingerspot.io/api/v1', '/');
        $this->token = $settings->get('fingerspot_api_token')?->value ?? '';
        $this->deviceId = $settings->get('fingerspot_device_id')?->value ?? null;
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->baseUrl);
    }

    public function fetchAttendanceLogs(string $dateFrom, string $dateTo): array
    {
        $response = Http::withToken($this->token)
            ->withHeaders(['Accept' => 'application/json'])
            ->get($this->baseUrl . '/attendance/logs', [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'device_id' => $this->deviceId,
                'limit' => 1000,
            ]);

        if ($response->failed()) {
            throw new \Exception('Finger Spot API error: ' . $response->body());
        }

        return $response->json()['data'] ?? $response->json() ?? [];
    }

    public function fetchEmployees(): array
    {
        $response = Http::withToken($this->token)
            ->withHeaders(['Accept' => 'application/json'])
            ->get($this->baseUrl . '/employees', [
                'device_id' => $this->deviceId,
                'limit' => 1000,
            ]);

        if ($response->failed()) {
            throw new \Exception('Finger Spot API error: ' . $response->body());
        }

        return $response->json()['data'] ?? $response->json() ?? [];
    }
}
