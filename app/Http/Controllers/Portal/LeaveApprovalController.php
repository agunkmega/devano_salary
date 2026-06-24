<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveApprovalController extends Controller
{
    public function index()
    {
        $headId = session('portal_employee_id');
        $department = Department::where('department_head_id', $headId)->first();

        if (!$department) {
            return redirect()->route('portal.dashboard')->with('error', 'Anda tidak terdaftar sebagai kepala departemen.');
        }

        $leaves = Leave::with(['employee', 'leaveType'])
            ->where('status', 'pending')
            ->where('employee_id', '!=', $headId)
            ->whereHas('employee', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            })
            ->latest()
            ->get();

        return view('portal.leave-approval.index', compact('leaves', 'department'));
    }

    public function approve(Leave $leave)
    {
        $headId = session('portal_employee_id');
        $department = Department::where('department_head_id', $headId)->first();

        if (!$department || $leave->employee->department_id !== $department->id) {
            return redirect()->route('portal.leave-approval.index')->with('error', 'Anda tidak berwenang menyetujui cuti ini.');
        }

        if ($leave->employee_id === $headId) {
            return redirect()->route('portal.leave-approval.index')->with('error', 'Anda tidak dapat menyetujui cuti sendiri.');
        }

        if ($leave->status !== 'pending') {
            return redirect()->route('portal.leave-approval.index')->with('error', 'Cuti ini sudah diproses.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by_head' => $headId,
            'approval_date' => Carbon::now(),
        ]);

        if ($leave->employee?->user_id) {
            Notification::create([
                'user_id' => $leave->employee->user_id,
                'title' => 'Cuti Disetujui',
                'message' => 'Pengajuan cuti ' . ($leave->leaveType?->name ?? '') . ' ' . $leave->total_days . ' hari telah disetujui oleh kepala departemen.',
                'type' => 'leave',
                'url' => route('portal.dashboard'),
                'icon' => 'check-circle',
            ]);
        }

        return redirect()->route('portal.leave-approval.index')->with('success', 'Cuti berhasil disetujui.');
    }

    public function reject(Request $request, Leave $leave)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $headId = session('portal_employee_id');
        $department = Department::where('department_head_id', $headId)->first();

        if (!$department || $leave->employee->department_id !== $department->id) {
            return redirect()->route('portal.leave-approval.index')->with('error', 'Anda tidak berwenang menolak cuti ini.');
        }

        if ($leave->employee_id === $headId) {
            return redirect()->route('portal.leave-approval.index')->with('error', 'Anda tidak dapat menolak cuti sendiri.');
        }

        if ($leave->status !== 'pending') {
            return redirect()->route('portal.leave-approval.index')->with('error', 'Cuti ini sudah diproses.');
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by_head' => $headId,
            'approval_date' => Carbon::now(),
            'rejection_reason' => $request->reason,
        ]);

        if ($leave->employee?->user_id) {
            Notification::create([
                'user_id' => $leave->employee->user_id,
                'title' => 'Cuti Ditolak',
                'message' => 'Pengajuan cuti ' . ($leave->leaveType?->name ?? '') . ' ' . $leave->total_days . ' hari ditolak oleh kepala departemen. Alasan: ' . $request->reason,
                'type' => 'leave',
                'url' => route('portal.dashboard'),
                'icon' => 'x-circle',
            ]);
        }

        return redirect()->route('portal.leave-approval.index')->with('success', 'Cuti berhasil ditolak.');
    }
}
