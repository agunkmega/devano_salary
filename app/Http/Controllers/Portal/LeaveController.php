<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveType;
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
            'reason' => $request->description,
            'status' => 'pending',
        ];

        if ($request->hasFile('file')) {
            $data['attachment'] = $request->file('file')->store('leaves', 'public');
        }

        Leave::create($data);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Pengajuan cuti berhasil dikirim.');
    }
}
