<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use App\Traits\LogsActivity;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    use LogsActivity;

    public function balance(Employee $employee)
    {
        [$ctId, $dpId, $ctQuota] = LeaveBalanceService::typeConfig();
        [$leaveYearStart, $leaveYearEnd, $leaveYearLabel] = LeaveBalanceService::leaveYear();

        $balance = LeaveBalanceService::forEmployee($employee, $leaveYearStart, $leaveYearEnd, $ctId, $dpId, $ctQuota);

        return response()->json($balance + ['leave_year' => $leaveYearLabel]);
    }

    public function index()
    {
        $leaves = Leave::with(['employee.user', 'employee.department', 'leaveType', 'approver'])
            ->when(!in_array(auth()->user()->role, ['super_admin', 'hr', 'manager']), function ($q) {
                $q->whereHas('employee', function ($sub) {
                    $sub->where('user_id', auth()->id());
                });
            })
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('leave_type_id'), function ($q, $typeId) {
                $q->where('leave_type_id', $typeId);
            })
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('start_date', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('end_date', '<=', $date);
            })
            ->when(request('employee'), function ($q, $employee) {
                $q->whereHas('employee', function ($sub) use ($employee) {
                    $sub->where('full_name', 'like', '%' . $employee . '%');
                });
            })
            ->latest()
            ->paginate(20);

        $leaveTypes = LeaveType::where('is_active', true)->get();

        $statusMap = [
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        $leavesData = $leaves->through(function ($leave) use ($statusMap) {
            $name = $leave->employee?->full_name ?? $leave->employee?->user?->name ?? 'Unknown';
            $words = preg_split('/\s+/', trim($name));
            $initials = strtoupper(
                substr($words[0] ?? '', 0, 1) .
                substr($words[1] ?? $words[0] ?? '', 0, 1)
            );
            $type = $leave->leaveType?->name ?? 'Cuti';
            $dateFrom = $leave->start_date->format('d M Y');
            $dateTo = $leave->end_date->format('d M Y');
            $dates = $dateFrom === $dateTo ? $dateFrom : $dateFrom . ' - ' . $dateTo;

            return [
                'id' => $leave->id,
                'employee' => $name,
                'initials' => $initials,
                'type' => $type,
                'dates' => $dates,
                'days' => $leave->total_days,
                'status' => $statusMap[$leave->status] ?? ucfirst($leave->status),
            ];
        });

        return view('leaves.index', compact('leaves', 'leavesData', 'leaveTypes'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();

        $employees = Employee::where('is_active', true)->get();

        return view('leaves.create', compact('leaveTypes', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'submission_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $validated['total_days'] = $start->diffInDays($end) + 1;
        $validated['status'] = 'pending';

        if (!$this->validateDpBalance($validated['employee_id'], $validated['leave_type_id'], $validated['total_days'])) {
            return back()->withInput()->with('error', 'Saldo DP pegawai tidak cukup.');
        }

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('leaves', 'public');
        }

        Leave::create($validated);

        $emp = Employee::find($validated['employee_id']);
        if ($emp && $emp->user_id) {
            Notification::create([
                'user_id' => $emp->user_id,
                'title' => 'Cuti Baru',
                'message' => 'Pengajuan cuti atas nama Anda telah dibuat oleh admin',
                'type' => 'leave',
                'icon' => 'calendar',
            ]);
        }

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave request created successfully.');
    }

    public function show(Leave $leave)
    {
        $leave->load(['employee.user', 'employee.department', 'employee.position', 'leaveType', 'approver', 'headApprover']);

        return view('leaves.show', compact('leave'));
    }

    public function edit(Leave $leave)
    {
        $leave->load('employee');
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();

        return view('leaves.edit', compact('leave', 'leaveTypes', 'employees'));
    }

    public function update(Request $request, Leave $leave)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'submission_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $validated['total_days'] = $start->diffInDays($end) + 1;

        if (!$this->validateDpBalance($validated['employee_id'], $validated['leave_type_id'], $validated['total_days'], $leave->id)) {
            return back()->withInput()->with('error', 'Saldo DP pegawai tidak cukup.');
        }

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('leaves', 'public');
        }

        $leave->update($validated);

        $this->logActivity('leave', 'Update', 'Mengupdate cuti ' . $leave->employee?->full_name, 'Leave', $leave->id);

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave updated successfully.');
    }

    public function approve($id)
    {
        $leave = Leave::findOrFail($id);

        if (!in_array(auth()->user()->role, ['super_admin', 'hr', 'manager'])) {
            return redirect()->route('admin.leaves.index')
                ->with('error', 'You do not have permission to approve leaves.');
        }

        if (!$this->validateDpBalance($leave->employee_id, $leave->leave_type_id, $leave->total_days, $leave->id)) {
            return redirect()->route('admin.leaves.index')
                ->with('error', 'Saldo DP pegawai tidak cukup untuk cuti ini.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approval_date' => now(),
        ]);

        $this->logActivity('leave', 'Approve', 'Menyetujui cuti ' . $leave->employee?->full_name, 'Leave', $leave->id);

        if ($leave->employee?->user_id) {
            Notification::create([
                'user_id' => $leave->employee->user_id,
                'title' => 'Cuti Disetujui',
                'message' => 'Pengajuan cuti ' . ($leave->leaveType?->name ?? '') . ' ' . $leave->total_days . ' hari telah disetujui',
                'type' => 'leave',
                'icon' => 'check',
            ]);
        }

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if (!in_array(auth()->user()->role, ['super_admin', 'hr', 'manager'])) {
            return redirect()->route('admin.leaves.index')
                ->with('error', 'You do not have permission to reject leaves.');
        }

        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        $leave->update([
            'status' => 'rejected',
            'notes' => $validated['notes'],
            'approved_by' => auth()->id(),
            'approval_date' => now(),
        ]);

        $this->logActivity('leave', 'Reject', 'Menolak cuti ' . $leave->employee?->full_name . ': ' . $validated['notes'], 'Leave', $leave->id);

        if ($leave->employee?->user_id) {
            Notification::create([
                'user_id' => $leave->employee->user_id,
                'title' => 'Cuti Ditolak',
                'message' => 'Pengajuan cuti ' . ($leave->leaveType?->name ?? '') . ' ditolak: ' . $validated['notes'],
                'type' => 'leave',
                'icon' => 'x',
            ]);
        }

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave rejected successfully.');
    }

    public function cancel($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->employee->user_id !== auth()->id() && !in_array(auth()->user()->role, ['super_admin', 'hr'])) {
            return redirect()->route('admin.leaves.index')
                ->with('error', 'You do not have permission to cancel this leave.');
        }

        if (!in_array($leave->status, ['pending', 'approved'])) {
            return redirect()->route('admin.leaves.index')
                ->with('error', 'Only pending or approved leaves can be cancelled.');
        }

        $leave->update(['status' => 'cancelled']);

        $this->logActivity('leave', 'Cancel', 'Membatalkan cuti ' . $leave->employee?->full_name, 'Leave', $leave->id);

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave cancelled successfully.');
    }

    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);

        if (!in_array(auth()->user()->role, ['super_admin', 'hr'])) {
            return redirect()->route('admin.leaves.index')
                ->with('error', 'You do not have permission to delete leaves.');
        }

        $leave->delete();

        $this->logActivity('leave', 'Delete', 'Menghapus cuti ' . $leave->employee?->full_name, 'Leave', $leave->id);

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave deleted successfully.');
    }

    public function myLeaves()
    {
        $leaves = Leave::with(['leaveType', 'approver'])
            ->whereHas('employee', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('leaves.my-leaves', compact('leaves', 'leaveTypes'));
    }

    private function validateDpBalance($employeeId, $leaveTypeId, int $days, ?int $excludeLeaveId = null): bool
    {
        $employee = Employee::find($employeeId);
        if (!$employee || $employee->dp_leave_type_id !== (int) $leaveTypeId) {
            return true;
        }

        $used = $employee->dp_used;
        if ($excludeLeaveId) {
            $excluded = Leave::find($excludeLeaveId);
            if ($excluded && $excluded->status === 'approved' && $excluded->leave_type_id === (int) $leaveTypeId) {
                $used -= (int) $excluded->total_days;
            }
        }

        return ($employee->dp_granted - $used) >= $days;
    }
}
