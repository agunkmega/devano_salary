<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FingerspotWebhookExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\FingerSpotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FingerSpotController extends Controller
{
    protected FingerSpotService $fingerSpot;

    public function __construct(FingerSpotService $fingerSpot)
    {
        $this->fingerSpot = $fingerSpot;
    }

    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::today();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::today();

        $query->whereBetween('attendance_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()]);

        if ($name = $request->name) {
            $query->whereHas('employee', fn ($q) => $q->where('full_name', 'like', "%$name%"));
        }

        $recent = $query->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $total = $recent->count();
        $today = Attendance::whereDate('attendance_date', Carbon::today())->count();

        return view('fingerspot.index', compact('recent', 'total', 'today', 'dateFrom', 'dateTo'));
    }

    public function fetch(Request $request)
    {
        return redirect()->route('admin.fingerspot.index')
            ->with('info', 'Fitur fetch tidak digunakan. Data otomatis masuk via webhook.');
    }

    public function import(Request $request)
    {
        return redirect()->route('admin.fingerspot.index')
            ->with('info', 'Fitur import tidak digunakan. Data otomatis masuk via webhook.');
    }

    public function sync(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::today()->subDays(2);
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::today();

        if ($dateFrom->greaterThan($dateTo)) {
            return redirect()->route('admin.fingerspot.machines')
                ->with('error', 'Tanggal awal tidak boleh melebihi tanggal akhir.');
        }

        $counts = ['created' => 0, 'updated' => 0, 'ignored' => 0, 'failed' => 0];
        $messages = ['success' => [], 'error' => []];

        $cursor = $dateFrom->copy();
        while ($cursor->lessThanOrEqualTo($dateTo)) {
            $chunkStart = $cursor->copy();
            $chunkEnd = $cursor->copy()->addDays(1)->min($dateTo);

            try {
                $records = $this->fingerSpot->fetchAttendanceLogs(
                    $chunkStart->format('Y-m-d'),
                    $chunkEnd->format('Y-m-d')
                );
            } catch (\Exception $e) {
                $messages['error'][] = $e->getMessage();
                $counts['failed']++;
                break;
            }

            collect($records)
                ->sortBy(fn ($r) => strtotime($r['scan'] ?? 0))
                ->each(function ($rec) use (&$counts) {
                    $result = $this->fingerSpot->processScan($rec);
                    $counts[$result['status'] === 'created' || $result['status'] === 'updated'
                        ? $result['status']
                        : 'ignored']++;
                });

            $cursor = $chunkEnd->copy()->addDay();
        }

        $summary = sprintf(
            'Sinkron selesai: %d dibuat, %d diperbarui, %d diabaikan.',
            $counts['created'],
            $counts['updated'],
            $counts['ignored']
        );

        if ($counts['failed'] > 0) {
            return redirect()->route('admin.fingerspot.machines')
                ->with('sync_summary', $summary)
                ->with('sync_errors', $messages['error'])
                ->with('error', 'Sinkron selesai dengan sebagian error.');
        }

        return redirect()->route('admin.fingerspot.machines')
            ->with('sync_summary', $summary)
            ->with('success', $summary);
    }

    public function exportExcel(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::today();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::today();
        $name = $request->name;

        return Excel::download(
            new FingerspotWebhookExport($dateFrom, $dateTo, $name),
            'riwayat-webhook-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function machines()
    {
        $settings = Setting::whereIn('key', [
            'fingerspot_api_url', 'fingerspot_api_token', 'fingerspot_device_id',
            'fingerspot_cloud_base_url', 'fingerspot_cloud_id',
        ])->get()->keyBy('key');

        $apiUrl = $settings->get('fingerspot_api_url')?->value ?? 'https://api.fingerspot.io/api/v1';
        $deviceId = $settings->get('fingerspot_device_id')?->value ?? '-';
        $apiToken = $settings->get('fingerspot_api_token')?->value;
        $cloudBaseUrl = $settings->get('fingerspot_cloud_base_url')?->value ?? 'https://developer.fingerspot.io';
        $cloudId = $settings->get('fingerspot_cloud_id')?->value ?? cache()->get('fingerspot_cloud_id', 'Belum ada data');

        $employeeCount = Employee::count();

        $lastWebhook = Attendance::where('notes', 'webhook')
            ->latest()->first();

        return view('fingerspot.machines', compact(
            'apiUrl', 'deviceId', 'apiToken', 'cloudBaseUrl', 'cloudId',
            'employeeCount', 'lastWebhook'
        ));
    }
}
