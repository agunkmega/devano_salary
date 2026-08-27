<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Absensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page { margin: 6mm; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 9px;
            color: #1e293b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page-break { page-break-before: always; }
        .header {
            text-align: center;
            padding-bottom: 4px;
            border-bottom: 2px solid #1e293b;
            margin-bottom: 4px;
        }
        .header h1 { font-size: 13px; font-weight: 700; }
        .header p { font-size: 9px; color: #64748b; }
        .employee-title {
            font-size: 15px;
            font-weight: 700;
            margin: 3px 0 1px;
            color: #0f172a;
        }
        .employee-title span {
            font-size: 10px;
            font-weight: 500;
            color: #64748b;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        th {
            padding: 3px 4px;
            text-align: left;
            font-size: 7px;
            font-weight: 600;
            color: #fff;
            background: #1e293b;
            text-transform: uppercase;
        }
        td {
            padding: 2px 4px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8px;
        }
        tr:nth-child(even) td { background: #f8fafc; }
        tbody tr.sunday td { background: #fecaca; }
        tbody tr.sunday td.day-cell { font-weight: 700; color: #dc2626; }
        .hadir, .terlambat { font-weight: 600; }
        .hadir { color: #059669; }
        .terlambat { color: #d97706; }
        .izin { color: #2563eb; }
        .sakit { color: #7c3aed; }
        .cuti { color: #0891b2; }
        .alpha { color: #dc2626; }
        .libur { color: #64748b; }
        .libur-nasional { color: #64748b; font-weight: 700; }
        .totals td {            font-weight: 700;
            background: #f1f5f9;
            border-top: 2px solid #1e293b;
            padding: 3px 4px;
            font-size: 8px;
        }
        .notes-section {
            margin-top: 4px;
            margin-bottom: 4px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 4px 6px;
        }
        .notes-section .notes-title {
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .notes-section .note-row {
            font-size: 8px;
            padding: 1px 0;
        }
        .notes-section .note-row span.note-date { color: #64748b; font-weight: 600; }
        @media print { .no-print { display: none; } }
        .no-print { text-align: center; margin-bottom: 6px; }
        .no-print button {
            background: #1e293b; color: #fff; border: none;
            padding: 4px 12px; border-radius: 3px; cursor: pointer;
            font-size: 9px; font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body>
    <div class="no-print"><button onclick="window.print()">Cetak</button></div>

    @forelse($employees as $emp)
    @if(!$loop->first)<div class="page-break"></div>@endif
    <div class="header">
        <h1>Laporan Absensi</h1>
        <div class="employee-title">{{ $emp['nama'] }} <span>— {{ $emp['jabatan'] }}</span></div>
        <p>Periode: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '-' }} s/d {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '-' }} | Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:12%;">Tanggal</th>
                <th style="width:8%;">Hari</th>
                <th style="width:10%;">Clock In</th>
                <th style="width:9%;">Break Out</th>
                <th style="width:9%;">Break In</th>
                <th style="width:10%;">Clock Out</th>
                <th style="width:10%;">Lembur</th>
                <th style="width:7%;">Telat</th>
                <th style="width:7%;">P. Awal</th>
                <th style="width:8%;">Ist. Lebih</th>
                <th style="width:10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTelat = 0; $totalPulangAwal = 0; $totalIstirahat = 0; $totalLembur = 0; $totalHadir = 0; $totalTerlambat = 0; $totalCuti = 0; $totalIzin = 0; $totalSakit = 0; $totalAlpha = 0;
            @endphp
            @foreach($emp['rows'] as $att)
            @php
                $statusLabels = ['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'cuti' => 'Cuti', 'alpha' => 'Alpha', 'libur' => 'Libur'];
                $statusClass = $att->status ?? 'alpha';
                $displayStatus = ($att->leave_type_name ?? null) ?: ($statusLabels[$statusClass] ?? ucfirst($att->status));
                if ($statusClass === 'libur' && !empty($att->holiday_name)) {
                    $displayStatus = 'Libur Nasional: ' . $att->holiday_name;
                }
                $totalTelat += (int) $att->late_minutes;
                $totalPulangAwal += (int) $att->early_leave_minutes;
                $totalIstirahat += (int) $att->excess_break_minutes;
                $totalLembur += (int) $att->overtime_minutes;
                if ($statusClass === 'hadir' || $statusClass === 'terlambat') $totalHadir++;
                if ($statusClass === 'terlambat') $totalTerlambat++;
                if ($statusClass === 'cuti') $totalCuti++;
                if ($statusClass === 'izin') $totalIzin++;
                if ($statusClass === 'sakit') $totalSakit++;
                if ($statusClass === 'alpha') $totalAlpha++;

                $lateDisplay = $att->ignore_late ? 'Diabaikan' : ($att->late_minutes > 0 ? $att->late_minutes . 'm' : '-');
                $earlyDisplay = $att->ignore_early_leave ? 'Diabaikan' : ($att->early_leave_minutes > 0 ? $att->early_leave_minutes . 'm' : '-');
                $breakDisplay = $att->ignore_excess_break ? 'Diabaikan' : ($att->excess_break_minutes > 0 ? $att->excess_break_minutes . 'm' : '-');
            @endphp
            <tr @if(\Carbon\Carbon::parse($att->attendance_date)->isSunday()) class="sunday" @endif>
                <td>{{ \Carbon\Carbon::parse($att->attendance_date)->format('d/m/Y') }}</td>
                <td class="day-cell {{ $statusClass === 'libur' ? 'libur' : '' }}">{{ $att->day_name ?? \Carbon\Carbon::parse($att->attendance_date)->locale('id')->dayName }}</td>
                <td>{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }}</td>
                <td>{{ $att->break_out ? \Carbon\Carbon::parse($att->break_out)->format('H:i') : '-' }}</td>
                <td>{{ $att->break_in ? \Carbon\Carbon::parse($att->break_in)->format('H:i') : '-' }}</td>
                <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }}</td>
                <td>{{ $att->overtime_in && $att->overtime_out ? \Carbon\Carbon::parse($att->overtime_in)->format('H:i') . '-' . \Carbon\Carbon::parse($att->overtime_out)->format('H:i') : '-' }}</td>
                <td>@if($lateDisplay === 'Diabaikan')<span style="color:#d97706;font-weight:600;">Diabaikan</span>@else{{ $lateDisplay }}@endif</td>
                <td>@if($earlyDisplay === 'Diabaikan')<span style="color:#7c3aed;font-weight:600;">Diabaikan</span>@else{{ $earlyDisplay }}@endif</td>
                <td>@if($breakDisplay === 'Diabaikan')<span style="color:#0891b2;font-weight:600;">Diabaikan</span>@else{{ $breakDisplay }}@endif</td>
                <td class="{{ $statusClass }} {{ $statusClass === 'libur' && !empty($att->holiday_name) ? 'libur-nasional' : '' }}">{{ $displayStatus }}</td>
            </tr>
            @endforeach
            <tr class="totals">
                <td colspan="6" style="text-align:right;">Total&nbsp;</td>
                <td>{{ $totalLembur > 0 ? $totalLembur . 'm' : '-' }}</td>
                <td>{{ $totalTelat > 0 ? $totalTelat . 'm' : '-' }}</td>
                <td>{{ $totalPulangAwal > 0 ? $totalPulangAwal . 'm' : '-' }}</td>
                <td>{{ $totalIstirahat > 0 ? $totalIstirahat . 'm' : '-' }}</td>
                <td>H{{ $totalHadir }} T{{ $totalTerlambat }} C{{ $totalCuti }} I{{ $totalIzin }} S{{ $totalSakit }} A{{ $totalAlpha }}</td>
            </tr>
        </tbody>
    </table>
    @if(isset($attendanceNotes[$emp['employee_id']]) && $attendanceNotes[$emp['employee_id']]->count())
    <div class="notes-section">
        <div class="notes-title">Catatan Absensi</div>
        @foreach($attendanceNotes[$emp['employee_id']] as $note)
        <div class="note-row">
            <span class="note-date">[{{ $note['date'] }}]</span> {{ $note['note'] }} <span style="color:#94a3b8;">— {{ $note['editor'] }}</span>
        </div>
        @endforeach
    </div>
    @endif
    @empty
    <p style="text-align:center;padding:20px;color:#94a3b8;">Tidak ada data absensi</p>
    @endforelse

    <script>window.print();</script>
</body>
</html>
