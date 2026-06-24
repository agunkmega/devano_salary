<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        return view('portal.leave.create', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:500',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        $data = [
            'employee_id' => session('portal_employee_id'),
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->description ?: '-',
            'status' => 'pending',
        ];

        if ($request->hasFile('file')) {
            $data['attachment'] = $request->file('file')->store('leaves', 'public');
        }

        Leave::create($data);

        $employee = Employee::with('department')->find(session('portal_employee_id'));
        $leaveType = LeaveType::find($request->leave_type_id);
        $admins = User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_HR])->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Cuti Baru',
                'message' => $employee?->full_name . ' mengajukan ' . ($leaveType?->name ?? 'cuti') . ' ' . $totalDays . ' hari',
                'type' => 'leave',
                'url' => route('admin.leaves.index', ['status' => 'pending']),
                'icon' => 'calendar',
            ]);
        }

        if ($employee?->department?->department_head_id) {
            $head = Employee::find($employee->department->department_head_id);
            if ($head && $head->user_id) {
                Notification::create([
                    'user_id' => $head->user_id,
                    'title' => 'Pengajuan Cuti Baru',
                    'message' => $employee->full_name . ' mengajukan ' . ($leaveType?->name ?? 'cuti') . ' ' . $totalDays . ' hari',
                    'type' => 'leave',
                    'url' => route('portal.leave-approval.index'),
                    'icon' => 'calendar',
                ]);
            }
        }

        return redirect()->route('portal.dashboard')
            ->with('success', 'Pengajuan cuti berhasil dikirim.');
    }
}
