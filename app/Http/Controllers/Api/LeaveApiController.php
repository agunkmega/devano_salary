<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->email) {
            $employee = Employee::where('email', $user->email)->first();
        }

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $leaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderBy('id', 'desc')
            ->get();

        $data = $leaves->map(function ($l) {
            $typeName = $l->leaveType?->name ?? 'Izin';
            $code = strtoupper($l->leaveType?->code ?? '');
            
            $category = 'ijin';
            if ($code === 'DP' || str_contains(strtoupper($typeName), 'DP') || str_contains(strtoupper($typeName), 'DAY OFF')) {
                $category = 'dp';
            } elseif ($code === 'CT' || str_contains(strtoupper($typeName), 'CUTI')) {
                $category = 'cuti';
            } elseif ($code === 'SK' || str_contains(strtoupper($typeName), 'SAKIT')) {
                $category = 'sakit';
            }

            return [
                'id' => $l->id,
                'leave_type_id' => $l->leave_type_id,
                'leave_type_name' => $typeName,
                'leave_type_category' => $category,
                'start_date' => $l->start_date?->format('Y-m-d'),
                'end_date' => $l->end_date?->format('Y-m-d'),
                'reason' => $l->reason,
                'status' => $l->status,
                'rejection_reason' => $l->rejection_reason,
                'approved_by_head' => (bool) $l->approved_by_head_id,
                'approved_by_hr' => (bool) $l->approved_by_hr_id,
                'created_at' => $l->created_at?->format('Y-m-d H:i'),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    public function quota(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->email) {
            $employee = Employee::where('email', $user->email)->first();
        }

        if (!$employee) {
            return response()->json([
                'data' => [
                    'annual_quota' => 12,
                    'total_quota' => 12,
                    'used_quota' => 0,
                    'remaining_quota' => 12,
                    'dp_granted' => 0,
                    'dp_used' => 0,
                    'dp_remaining' => 0,
                ],
            ]);
        }

        // Calculation matched with Portal/DashboardController@index
        $now = now();
        if ($now->month === 12 && $now->day >= 26) {
            $leaveYearStart = \Carbon\Carbon::create($now->year, 12, 26)->startOfDay();
            $leaveYearEnd = \Carbon\Carbon::create($now->year + 1, 12, 25)->endOfDay();
            $leaveYearLabel = $now->year . '/' . ($now->year + 1);
        } else {
            $leaveYearStart = \Carbon\Carbon::create($now->year - 1, 12, 26)->startOfDay();
            $leaveYearEnd = \Carbon\Carbon::create($now->year, 12, 25)->endOfDay();
            $leaveYearLabel = ($now->year - 1) . '/' . $now->year;
        }

        $tenureDays = $employee->join_date ? $employee->join_date->diffInDays(now()) : 0;
        $eligible = $employee->cuti_eligible && $tenureDays >= 365;

        $effectiveCtQuota = 0;
        if ($eligible && $employee->join_date) {
            $anniversary = $employee->join_date->copy()->addYear();
            if ($anniversary->lte($leaveYearStart)) {
                $effectiveCtQuota = 12;
            } elseif ($anniversary->gt($leaveYearEnd)) {
                $effectiveCtQuota = 0;
            } else {
                $effectiveCtQuota = min(12, ($leaveYearEnd->year - $anniversary->year) * 12 + ($leaveYearEnd->month - $anniversary->month) + 1);
            }
        }

        // Used Cuti Tahunan in leave year period (26 Dec to 25 Dec)
        $ctType = \App\Models\LeaveType::whereIn('code', ['CT', 'CUTI'])->first();
        $ctTypeId = $ctType ? $ctType->id : 1;

        $ctUsed = Leave::where('employee_id', $employee->id)
            ->where('leave_type_id', $ctTypeId)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$leaveYearStart, $leaveYearEnd])
            ->get()
            ->sum(fn($l) => $l->start_date->diffInDays($l->end_date) + 1);

        $ctRemaining = max(0, $effectiveCtQuota - $ctUsed);

        // DP (Day Off Replacement / Hutang Libur)
        $dpGranted = (int) ($employee->dp_granted ?? 0);
        $dpUsed = (int) ($employee->dp_used ?? 0);
        $dpRemaining = (int) ($employee->dp_remaining ?? max(0, $dpGranted - $dpUsed));

        return response()->json([
            'data' => [
                'annual_quota' => $effectiveCtQuota,
                'total_quota' => $effectiveCtQuota,
                'used_quota' => $ctUsed,
                'remaining_quota' => $ctRemaining,
                'dp_granted' => $dpGranted,
                'dp_used' => $dpUsed,
                'dp_remaining' => $dpRemaining,
                'leave_year_label' => $leaveYearLabel,
                'eligible' => $eligible,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->email) {
            $employee = Employee::where('email', $user->email)->first();
        }

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leaves', 'public');
        }

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $totalDays = max(1, $startDate->diffInDays($endDate) + 1);

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'submission_date' => now()->toDateString(),
            'reason' => $validated['reason'],
            'status' => 'pending',
            'attachment' => $attachmentPath,
        ]);

        \App\Services\NotificationService::notifyLeaveSubmission($leave);

        return response()->json([
            'message' => 'Pengajuan cuti/DP berhasil dikirim.',
            'data' => [
                'id' => $leave->id,
                'leave_type_id' => $leave->leave_type_id,
                'start_date' => $leave->start_date?->format('Y-m-d'),
                'end_date' => $leave->end_date?->format('Y-m-d'),
                'reason' => $leave->reason,
                'status' => $leave->status,
            ],
        ], 201);
    }

    public function cancel($id, Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->email) {
            $employee = Employee::where('email', $user->email)->first();
        }

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $leave = Leave::where('employee_id', $employee->id)
            ->where('id', $id)
            ->first();

        if (!$leave) {
            return response()->json(['message' => 'Data pengajuan tidak ditemukan.'], 404);
        }

        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'Hanya pengajuan berstatus pending yang dapat dibatalkan.'], 422);
        }

        $leave->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Pengajuan berhasil dibatalkan.',
        ]);
    }

    public function leaveTypes()
    {
        $types = LeaveType::where('is_active', true)->get();

        if ($types->isEmpty()) {
            return response()->json([
                'data' => [
                    ['id' => 1, 'name' => 'Cuti Tahunan (Potong Quota)', 'category' => 'cuti'],
                    ['id' => 4, 'name' => 'DP - Day Off Replacement (Hak Libur)', 'category' => 'dp'],
                    ['id' => 2, 'name' => 'Sakit dengan Surat Dokter', 'category' => 'sakit'],
                    ['id' => 6, 'name' => 'Izin Keperluan Pribadi', 'category' => 'ijin'],
                ],
            ]);
        }

        return response()->json([
            'data' => $types->map(function ($t) {
                $code = strtoupper($t->code ?? '');
                $name = strtoupper($t->name ?? '');

                $category = 'ijin';
                if ($code === 'DP' || str_contains($name, 'DP') || str_contains($name, 'DAY OFF')) {
                    $category = 'dp';
                } elseif ($code === 'CT' || str_contains($name, 'CUTI')) {
                    $category = 'cuti';
                } elseif ($code === 'SK' || str_contains($name, 'SAKIT')) {
                    $category = 'sakit';
                }

                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'category' => $category,
                ];
            }),
        ]);
    }

    public function approvals(Request $request)
    {
        $user = $request->user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->email) {
            $employee = \App\Models\Employee::where('email', $user->email)->first();
        }

        if (!$employee) {
            return response()->json([
                'is_department_head' => false,
                'department_name' => null,
                'pending_count' => 0,
                'data' => [],
            ]);
        }

        $department = \App\Models\Department::where('department_head_id', $employee->id)->first();
        if (!$department) {
            return response()->json([
                'is_department_head' => false,
                'department_name' => null,
                'pending_count' => 0,
                'data' => [],
            ]);
        }

        $status = $request->query('status', 'all');

        $baseQuery = Leave::with(['employee.department', 'leaveType'])
            ->where('employee_id', '!=', $employee->id)
            ->whereHas('employee', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            });

        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();

        $query = clone $baseQuery;
        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'history') {
            $query->whereIn('status', ['approved', 'rejected']);
        } elseif ($status !== 'all' && in_array($status, ['approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();

        $data = $leaves->map(function ($l) use ($department) {
            $emp = $l->employee;
            $attachmentUrl = null;
            if ($l->attachment) {
                $attachmentUrl = str_starts_with($l->attachment, 'http')
                    ? $l->attachment
                    : asset('storage/' . $l->attachment);
            }

            $avatarUrl = null;
            if ($emp?->photo) {
                $avatarUrl = str_starts_with($emp->photo, 'http')
                    ? $emp->photo
                    : asset('storage/' . $emp->photo);
            }

            return [
                'id' => $l->id,
                'employee_id' => $l->employee_id,
                'employee_name' => $emp?->full_name ?? 'Pegawai',
                'employee_nik' => $emp?->nik ?? '-',
                'employee_avatar' => $avatarUrl,
                'department_name' => $department->name,
                'leave_type_id' => $l->leave_type_id,
                'leave_type_name' => $l->leaveType?->name ?? 'Izin / Cuti',
                'leave_type_code' => $l->leaveType?->code ?? '',
                'start_date' => $l->start_date?->format('Y-m-d'),
                'end_date' => $l->end_date?->format('Y-m-d'),
                'total_days' => $l->total_days ?? 1,
                'submission_date' => $l->submission_date?->format('Y-m-d') ?? $l->created_at?->format('Y-m-d'),
                'reason' => $l->reason,
                'attachment_url' => $attachmentUrl,
                'status' => $l->status,
                'rejection_reason' => $l->rejection_reason,
                'approved_by_head' => $l->approved_by_head,
                'approval_date' => $l->approval_date?->format('Y-m-d H:i'),
                'dp_remaining' => (int) ($emp?->dp_remaining ?? 0),
                'created_at' => $l->created_at?->format('Y-m-d H:i'),
            ];
        });

        return response()->json([
            'is_department_head' => true,
            'department_id' => $department->id,
            'department_name' => $department->name,
            'pending_count' => $pendingCount,
            'data' => $data,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $user = $request->user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->email) {
            $employee = \App\Models\Employee::where('email', $user->email)->first();
        }

        if (!$employee) {
            return response()->json(['message' => 'Employee tidak ditemukan.'], 404);
        }

        $department = \App\Models\Department::where('department_head_id', $employee->id)->first();
        if (!$department) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses sebagai Kepala Departemen.'], 403);
        }

        $leave = Leave::with(['employee', 'leaveType'])->find($id);
        if (!$leave) {
            return response()->json(['message' => 'Data pengajuan cuti tidak ditemukan.'], 404);
        }

        if (!$leave->employee || $leave->employee->department_id !== $department->id) {
            return response()->json(['message' => 'Anda tidak berwenang menyetujui cuti pegawai ini.'], 403);
        }

        if ($leave->employee_id === $employee->id) {
            return response()->json(['message' => 'Anda tidak dapat menyetujui pengajuan cuti Anda sendiri.'], 422);
        }

        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'Pengajuan cuti ini sudah diproses sebelumnya.'], 422);
        }

        $subordinate = $leave->employee;
        if ($subordinate && strtoupper($leave->leaveType?->code ?? '') === 'DP' && $leave->total_days > ($subordinate->dp_remaining ?? 0)) {
            return response()->json([
                'message' => 'Saldo DP pegawai tidak mencukupi (' . ($subordinate->dp_remaining ?? 0) . ' hari tersisa).'
            ], 422);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by_head' => $employee->id,
            'approval_date' => \Carbon\Carbon::now(),
        ]);

        if ($subordinate?->user_id) {
            \App\Services\NotificationService::sendToUser(
                $subordinate->user_id,
                'Cuti Disetujui',
                'Pengajuan ' . ($leave->leaveType?->name ?? 'Cuti') . ' (' . $leave->total_days . ' hari) telah disetujui oleh Kepala Departemen (' . $employee->full_name . ').',
                'leave',
                '/leaves'
            );
        }

        return response()->json([
            'message' => 'Pengajuan cuti berhasil disetujui.',
            'data' => [
                'id' => $leave->id,
                'status' => $leave->status,
                'approval_date' => $leave->approval_date?->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = $request->user();
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->email) {
            $employee = \App\Models\Employee::where('email', $user->email)->first();
        }

        if (!$employee) {
            return response()->json(['message' => 'Employee tidak ditemukan.'], 404);
        }

        $department = \App\Models\Department::where('department_head_id', $employee->id)->first();
        if (!$department) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses sebagai Kepala Departemen.'], 403);
        }

        $leave = Leave::with(['employee', 'leaveType'])->find($id);
        if (!$leave) {
            return response()->json(['message' => 'Data pengajuan cuti tidak ditemukan.'], 404);
        }

        if (!$leave->employee || $leave->employee->department_id !== $department->id) {
            return response()->json(['message' => 'Anda tidak berwenang menolak cuti pegawai ini.'], 403);
        }

        if ($leave->employee_id === $employee->id) {
            return response()->json(['message' => 'Anda tidak dapat menolak pengajuan cuti Anda sendiri.'], 422);
        }

        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'Pengajuan cuti ini sudah diproses sebelumnya.'], 422);
        }

        $subordinate = $leave->employee;

        $leave->update([
            'status' => 'rejected',
            'approved_by_head' => $employee->id,
            'approval_date' => \Carbon\Carbon::now(),
            'rejection_reason' => $validated['reason'],
        ]);

        if ($subordinate?->user_id) {
            \App\Services\NotificationService::sendToUser(
                $subordinate->user_id,
                'Cuti Ditolak',
                'Pengajuan ' . ($leave->leaveType?->name ?? 'Cuti') . ' ditolak oleh Kepala Departemen (' . $employee->full_name . '): ' . $validated['reason'],
                'leave',
                '/leaves'
            );
        }

        return response()->json([
            'message' => 'Pengajuan cuti telah ditolak.',
            'data' => [
                'id' => $leave->id,
                'status' => $leave->status,
                'rejection_reason' => $leave->rejection_reason,
                'approval_date' => $leave->approval_date?->format('Y-m-d H:i'),
            ],
        ]);
    }
}

