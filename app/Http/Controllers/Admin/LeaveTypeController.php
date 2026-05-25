<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::latest()->paginate(20);
        return view('leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('leave-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:leave_types,code',
            'description' => 'nullable|string',
            'is_paid' => 'nullable|boolean',
            'max_days_per_year' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['is_active'] = $request->boolean('is_active');

        LeaveType::create($validated);

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Jenis cuti berhasil ditambahkan.');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:leave_types,code,' . $leaveType->id,
            'description' => 'nullable|string',
            'is_paid' => 'nullable|boolean',
            'max_days_per_year' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['is_active'] = $request->boolean('is_active');

        $leaveType->update($validated);

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Jenis cuti berhasil diperbarui.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Jenis cuti berhasil dihapus.');
    }
}