<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')
            ->with('departmentHead')
            ->when(request('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        $employees = Employee::where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nik']);

        return view('departments.index', compact('departments', 'employees'));
    }

    public function create()
    {
        return redirect()->route('admin.departments.index');
    }

    public function edit(Department $department)
    {
        return redirect()->route('admin.departments.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'department_head_id' => 'nullable|exists:employees,id',
        ]);

        $validated['department_head_id'] = !empty($validated['department_head_id']) ? $validated['department_head_id'] : null;

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'department_head_id' => 'nullable|exists:employees,id',
        ]);

        $validated['department_head_id'] = !empty($validated['department_head_id']) ? $validated['department_head_id'] : null;

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Cannot delete department with active employees.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
