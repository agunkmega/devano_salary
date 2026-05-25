<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::withCount('employees')
            ->when(request('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        $shiftsData = $shifts->getCollection()->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'clock_in_time' => $s->clock_in_time ? substr($s->clock_in_time, 0, 5) : '',
            'clock_out_time' => $s->clock_out_time ? substr($s->clock_out_time, 0, 5) : '',
            'saturday_clock_out_time' => $s->saturday_clock_out_time ? substr($s->saturday_clock_out_time, 0, 5) : '',
            'break_start' => $s->break_start ? substr($s->break_start, 0, 5) : '',
            'break_end' => $s->break_end ? substr($s->break_end, 0, 5) : '',
            'late_tolerance_minutes' => $s->late_tolerance_minutes,
            'is_active' => $s->is_active,
            'employees_count' => $s->employees_count ?? 0,
        ])->values();

        return view('shifts.index', compact('shifts', 'shiftsData'));
    }

    public function create()
    {
        return redirect()->route('admin.shifts.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shifts,code',
            'clock_in_time' => 'required|date_format:H:i',
            'clock_out_time' => 'required|date_format:H:i',
            'saturday_clock_out_time' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'late_tolerance_minutes' => 'nullable|integer|min:0',
            'is_night_shift' => 'boolean',
            'working_days' => 'nullable|array',
            'working_days.*' => 'string|in:mon,tue,wed,thu,fri,sat,sun',
            'is_active' => 'boolean',
        ]);

        if (!isset($validated['working_days'])) {
            $validated['working_days'] = ['mon', 'tue', 'wed', 'thu', 'fri'];
        }

        Shift::create($validated);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    public function edit(Shift $shift)
    {
        return redirect()->route('admin.shifts.index');
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shifts,code,' . $shift->id,
            'clock_in_time' => 'required|date_format:H:i',
            'clock_out_time' => 'required|date_format:H:i',
            'saturday_clock_out_time' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'late_tolerance_minutes' => 'nullable|integer|min:0',
            'is_night_shift' => 'boolean',
            'working_days' => 'nullable|array',
            'working_days.*' => 'string|in:mon,tue,wed,thu,fri,sat,sun',
            'is_active' => 'boolean',
        ]);

        if (!isset($validated['working_days'])) {
            $validated['working_days'] = ['mon', 'tue', 'wed', 'thu', 'fri'];
        }

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift)
    {
        if ($shift->employees()->count() > 0) {
            return redirect()->route('admin.shifts.index')
                ->with('error', 'Cannot delete shift assigned to employees.');
        }

        $shift->delete();

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }
}
