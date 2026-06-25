<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Exports\FingerspotWebhookExport;
use App\Models\Setting;
use App\Services\FingerSpotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            $query->whereHas('employee', fn($q) => $q->where('full_name', 'like', "%$name%"));
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

    public function exportExcel(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::today();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::today();
        $name = $request->name;

        return Excel::download(
            new FingerspotWebhookExport($dateFrom, $dateTo, $name),
            'riwayat-webhook-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function machines()
    {
        $settings = Setting::whereIn('key', [
            'fingerspot_api_url', 'fingerspot_api_token', 'fingerspot_device_id',
        ])->get()->keyBy('key');

        $apiUrl = $settings->get('fingerspot_api_url')?->value ?? 'https://api.fingerspot.io/api/v1';
        $deviceId = $settings->get('fingerspot_device_id')?->value ?? '-';
        $apiToken = $settings->get('fingerspot_api_token')?->value;

        $employeeCount = Employee::count();

        $lastWebhook = Attendance::where('notes', 'webhook')
            ->latest()->first();

        $cloudId = cache()->get('fingerspot_cloud_id', 'Belum ada data');

        return view('fingerspot.machines', compact(
            'apiUrl', 'deviceId', 'apiToken',
            'employeeCount', 'lastWebhook', 'cloudId'
        ));
    }
}
