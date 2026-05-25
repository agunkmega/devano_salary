<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('department')->withCount('employees')
            ->when(request('department_id'), function ($q, $deptId) {
                $q->where('department_id', $deptId);
            })
            ->when(request('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        $departments = Department::where('is_active', true)->get();

        $positionsData = $positions->getCollection()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'department_id' => $p->department_id,
            'department_name' => $p->department->name ?? '',
            'description' => $p->description,
            'employees_count' => $p->employees_count ?? 0,
        ])->values();

        return view('positions.index', compact('positions', 'departments', 'positionsData'));
    }

    public function create()
    {
        return redirect()->route('admin.positions.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:positions,code',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Position::create($validated);

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        return redirect()->route('admin.positions.index');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:positions,code,' . $position->id,
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $position->update($validated);

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->count() > 0) {
            return redirect()->route('admin.positions.index')
                ->with('error', 'Cannot delete position with active employees.');
        }

        $position->delete();

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position deleted successfully.');
    }
}
