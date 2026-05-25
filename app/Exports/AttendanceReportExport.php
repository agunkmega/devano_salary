<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $departmentId;

    public function __construct($startDate, $endDate, $departmentId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->departmentId = $departmentId;
    }

    public function collection()
    {
        $query = Attendance::with(['employee.user', 'employee.department', 'employee.position', 'shift'])
            ->whereBetween('attendance_date', [$this->startDate, $this->endDate]);

        if ($this->departmentId) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentId);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'NIK',
            'Employee Name',
            'Department',
            'Position',
            'Shift',
            'Clock In',
            'Clock Out',
            'Status',
            'Late (Minutes)',
            'Early Leave (Minutes)',
            'Overtime (Minutes)',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->attendance_date?->format('Y-m-d'),
            $attendance->employee?->nik,
            $attendance->employee?->full_name,
            $attendance->employee?->department?->name,
            $attendance->employee?->position?->name,
            $attendance->shift?->name,
            $attendance->clock_in?->format('H:i:s'),
            $attendance->clock_out?->format('H:i:s'),
            $attendance->status,
            $attendance->late_minutes,
            $attendance->early_leave_minutes,
            $attendance->overtime_minutes,
        ];
    }
}
