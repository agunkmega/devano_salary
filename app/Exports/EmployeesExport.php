<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Employee::with(['department', 'position'])->get();
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Name',
            'Department',
            'Position',
            'No. HP',
            'Base Salary',
            'Allowance',
            'Allowance Type',
            'Allowance Absensi',
            'Allowance Transport',
            'Allowance Jabatan',
            'Allowance Insentif',
            'Overtime Pay/Hour',
            'Uang Makan Lembur',
            'BPJS Kesehatan Tanggungan',
            'Employee Type',
            'Status',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->nik,
            $employee->full_name,
            $employee->department?->name,
            $employee->position?->name,
            $employee->phone,
            $employee->base_salary,
            $employee->allowance,
            $employee->allowance_type,
            $employee->allowance_absensi,
            $employee->allowance_transport,
            $employee->allowance_jabatan,
            $employee->allowance_insentif,
            $employee->overtime_pay_per_hour,
            $employee->uang_makan_lembur,
            $employee->bpjs_kesehatan_tanggungan,
            $employee->employee_type === 'harian' ? 'Harian' : 'Bulanan',
            $employee->is_active ? 'Active' : 'Inactive',
        ];
    }
}
