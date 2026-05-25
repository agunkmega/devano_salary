<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['user', 'department', 'position', 'shift'])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->where('department_id', $deptId);
            })
            ->when(request('position_id'), function ($q, $posId) {
                $q->where('position_id', $posId);
            })
            ->when(request('status'), function ($q, $status) {
                if ($status === 'active') {
                    $q->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->when(request('employee_type'), function ($q, $type) {
                $q->where('employee_type', $type);
            })
            ->latest()
            ->paginate(20);

        $departments = Department::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();

        $employeesData = $employees->getCollection()->map(function ($e) {
            $words = explode(' ', $e->full_name);
            $initials = '';
            foreach (array_slice($words, 0, 2) as $w) {
                $initials .= strtoupper(substr($w, 0, 1));
            }
            return [
                'id' => $e->id,
                'nik' => $e->nik,
                'name' => $e->full_name,
                'initials' => $initials,
                'email' => $e->email,
                'department' => $e->department->name ?? '',
                'position' => $e->position->name ?? '',
                'status' => $e->is_active ? 'active' : 'inactive',
                'status_label' => $e->is_active ? 'Aktif' : 'Non-Aktif',
                'employee_type' => $e->employee_type ?? 'bulanan',
            ];
        })->values();

        return view('employees.index', compact('employees', 'departments', 'positions', 'employeesData'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();
        $shifts = Shift::where('is_active', true)->get();

        return view('employees.create', compact('departments', 'positions', 'shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|max:50|unique:employees,nik',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:L,P',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'base_salary' => 'nullable|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'allowance_type' => 'nullable|string|max:100',
            'allowance_absensi' => 'nullable|numeric|min:0',
            'allowance_transport' => 'nullable|numeric|min:0',
            'allowance_jabatan' => 'nullable|numeric|min:0',
            'allowance_insentif' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:100',
            'bank_holder' => 'nullable|string|max:255',
            'bpjs_ketenagakerjaan' => 'nullable|string|max:50',
            'bpjs_ketenagakerjaan_type' => 'nullable|in:full,partial',
            'bpjs_kesehatan' => 'nullable|string|max:50',
            'bpjs_kesehatan_active' => 'nullable|boolean',
            'bpjs_kesehatan_tanggungan' => 'nullable|integer|min:0',
            'iuran_wajib_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'employee_type' => 'nullable|in:bulanan,harian',
            'overtime_pay_per_hour' => 'nullable|numeric|min:0',
            'uang_makan_lembur' => 'nullable|numeric|min:0',
        ]);

        $password = Str::random(8);


        $user = User::create([
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $validated['user_id'] = $user->id;
        $validated['bpjs_kesehatan_active'] = $request->boolean('bpjs_kesehatan_active');
        $employee = Employee::create($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee created successfully. Login credentials: Email: {$user->email}, Password: {$password}");
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'position', 'shift', 'attendances' => function ($q) {
            $q->latest()->take(30);
        }, 'leaves' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();
        $shifts = Shift::where('is_active', true)->get();

        return view('employees.edit', compact('employee', 'departments', 'positions', 'shifts'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'nik' => 'required|string|max:50|unique:employees,nik,' . $employee->id,
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:L,P',
            'address' => 'nullable|string',
            'join_date' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'base_salary' => 'nullable|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'allowance_type' => 'nullable|string|max:100',
            'allowance_absensi' => 'nullable|numeric|min:0',
            'allowance_transport' => 'nullable|numeric|min:0',
            'allowance_jabatan' => 'nullable|numeric|min:0',
            'allowance_insentif' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:100',
            'bank_holder' => 'nullable|string|max:255',
            'bpjs_ketenagakerjaan' => 'nullable|string|max:50',
            'bpjs_ketenagakerjaan_type' => 'nullable|in:full,partial',
            'bpjs_kesehatan' => 'nullable|string|max:50',
            'bpjs_kesehatan_active' => 'nullable|boolean',
            'bpjs_kesehatan_tanggungan' => 'nullable|integer|min:0',
            'iuran_wajib_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'employee_type' => 'nullable|in:bulanan,harian',
            'overtime_pay_per_hour' => 'nullable|numeric|min:0',
            'uang_makan_lembur' => 'nullable|numeric|min:0',
        ]);

        if ($employee->user) {
            $employee->user->update([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
            ]);
        }

        $validated['bpjs_kesehatan_active'] = $request->boolean('bpjs_kesehatan_active');

        $employee->update($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->update(['is_active' => false]);

        if ($employee->user) {
            $employee->user->update(['is_active' => false]);
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new EmployeesImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $msg = "Imported {$import->getRowCount()} employees successfully.";
            if ($errors) {
                $msg .= ' Errors: ' . implode('; ', array_slice($errors, 0, 5));
            }

            return redirect()->route('admin.employees.index')
                ->with('success', $msg);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $msgs = [];
            foreach (array_slice($failures, 0, 5) as $f) {
                $msgs[] = "Row {$f->row()}: " . implode(', ', $f->errors());
            }
            return redirect()->route('admin.employees.index')
                ->with('error', 'Import failed: ' . implode(' | ', $msgs));
        } catch (\Exception $e) {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        return Excel::download(new EmployeesExport, 'employees-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $employees = Employee::with(['user', 'department', 'position'])->get();
        $pdf = Pdf::loadView('employees.pdf', compact('employees'));

        return $pdf->download('employees-' . now()->format('Y-m-d') . '.pdf');
    }
}
