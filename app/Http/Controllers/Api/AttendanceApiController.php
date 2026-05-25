<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function push(Request $request)
    {
        $validated = $request->validate([
            'records' => 'required|array',
            'records.*.pin' => 'required|string',
            'records.*.timestamp' => 'required|date_format:Y-m-d H:i:s',
            'machine_sn' => 'nullable|string',
        ]);

        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $record) {
            $employee = Employee::where('nik', $record['pin'])->first();

            if (!$employee) {
                $failed++;
                $errors[] = "Employee with PIN {$record['pin']} not found";
                continue;
            }

            try {
                $dateTime = Carbon::parse($record['timestamp']);

                $this->attendanceService->syncAttendance(
                    $employee->id,
                    $dateTime->format('Y-m-d'),
                    $dateTime
                );

                AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'log_date' => $dateTime->toDateString(),
                    'log_time' => $dateTime->toTimeString(),
                    'type' => 'auto',
                    'pin' => $record['pin'],
                    'machine_sn' => $validated['machine_sn'] ?? null,
                    'raw_data' => json_encode($record),
                    'status' => 'processed',
                ]);

                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Error processing {$record['pin']}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} records, {$failed} failed",
            'data' => [
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ],
        ]);
    }

    public function logs(Request $request)
    {
        $logs = AttendanceLog::with('employee')
            ->when($request->date_from, fn($q, $d) => $q->whereDate('log_date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('log_date', '<=', $d))
            ->when($request->pin, fn($q, $p) => $q->where('pin', $p))
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,csv|max:10240',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            $result = $extension === 'csv'
                ? $this->attendanceService->processCsvImport($file->getRealPath(), 0)
                : $this->attendanceService->processTxtImport($file->getRealPath(), 0);

            return response()->json([
                'success' => true,
                'message' => "Imported {$result['imported']} records",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}