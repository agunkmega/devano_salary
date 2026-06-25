<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\WithHeadings;
    use Maatwebsite\Excel\Concerns\WithMapping;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FingerspotWebhookExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $name;

    public function __construct($dateFrom, $dateTo, $name = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->name = $name;
    }

    public function collection()
    {
        $query = Attendance::with('employee')
            ->whereBetween('attendance_date', [$this->dateFrom->startOfDay(), $this->dateTo->endOfDay()]);

        if ($this->name) {
            $query->whereHas('employee', fn($q) => $q->where('full_name', 'like', "%{$this->name}%"));
        }

        return $query->orderBy('attendance_date', 'desc')->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'NIK', 'Nama', 'Tanggal', 'Clock In', 'Break Out', 'Break In', 'Clock Out',
            'Lembur In', 'Lembur Out', 'Status', 'Keterangan',
        ];
    }

    public function map($a): array
    {
        return [
            $a->employee?->nik,
            $a->employee?->full_name,
            $a->attendance_date instanceof Carbon ? $a->attendance_date->format('Y-m-d') : $a->attendance_date,
            $a->clock_in ? Carbon::parse($a->clock_in)->format('H:i:s') : '',
            $a->break_out ? Carbon::parse($a->break_out)->format('H:i:s') : '',
            $a->break_in ? Carbon::parse($a->break_in)->format('H:i:s') : '',
            $a->clock_out ? Carbon::parse($a->clock_out)->format('H:i:s') : '',
            $a->overtime_in ? Carbon::parse($a->overtime_in)->format('H:i:s') : '',
            $a->overtime_out ? Carbon::parse($a->overtime_out)->format('H:i:s') : '',
            $a->status,
            $a->notes,
        ];
    }
}
