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

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $leaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderBy('id', 'desc')
            ->get();

        $data = $leaves->map(function ($l) {
            return [
                'id' => $l->id,
                'leave_type_id' => $l->leave_type_id,
                'leave_type_name' => $l->leaveType?->name ?? 'Izin',
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

        $totalQuota = 12;
        $used = 0;

        if ($employee) {
            $used = Leave::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->count();
        }

        $remaining = max(0, $totalQuota - $used);

        return response()->json([
            'data' => [
                'annual_quota' => $totalQuota,
                'used_quota' => $used,
                'remaining_quota' => $remaining,
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

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leaves', 'public');
        }

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => 'pending',
            'attachment' => $attachmentPath,
        ]);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dikirim.',
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

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $leave = Leave::where('employee_id', $employee->id)
            ->where('id', $id)
            ->first();

        if (!$leave) {
            return response()->json(['message' => 'Data cuti tidak ditemukan.'], 404);
        }

        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'Hanya pengajuan berstatus pending yang dapat dibatalkan.'], 422);
        }

        $leave->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dibatalkan.',
        ]);
    }

    public function leaveTypes()
    {
        $types = LeaveType::where('is_active', true)->get();

        if ($types->isEmpty()) {
            return response()->json([
                'data' => [
                    ['id' => 1, 'name' => 'Cuti Tahunan', 'category' => 'cuti'],
                    ['id' => 2, 'name' => 'Izin Sakit', 'category' => 'sakit'],
                    ['id' => 3, 'name' => 'Izin Keperluan Pribadi', 'category' => 'ijin'],
                ],
            ]);
        }

        return response()->json([
            'data' => $types->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'category' => $t->is_paid ? 'cuti' : 'ijin',
            ]),
        ]);
    }
}
