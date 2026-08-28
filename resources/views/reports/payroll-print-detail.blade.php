<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Payroll Resume - {{ $period ?? 'Semua Periode' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page { margin: 5mm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 7px;
            color: #1e293b;
        }
        h1 { font-size: 12px; margin-bottom: 2px; }
        .sub { font-size: 8px; color: #64748b; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 3px 4px;
            text-align: left;
            font-size: 6px;
            font-weight: 600;
            color: #fff;
            background: #2563eb;
            text-transform: uppercase;
            white-space: nowrap;
        }
        th.right, td.right { text-align: right; }
        td {
            padding: 2px 4px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        tr:nth-child(even) td { background: #f8fafc; }
        .totals td {
            font-weight: 700;
            background: #eff6ff;
            border-top: 2px solid #2563eb;
            padding: 3px 4px;
        }
        .badge {
            font-size: 6px;
            padding: 1px 4px;
            border-radius: 2px;
            font-weight: 600;
        }
        .badge-b { background: #dbeafe; color: #1d4ed8; }
        .badge-h { background: #ffedd5; color: #c2410c; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <h1>Payroll Resume</h1>
    <p class="sub">Periode: {{ $period ?? 'Semua Periode' }}@if($stationName ?? null) | Station: {{ $stationName }}@endif | {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th class="right">Jenis</th>
                <th class="right">Gaji Pokok</th>
                <th class="right">Tunjangan</th>
                <th class="right">Lembur</th>
                <th class="right">U.Makan</th>
                <th class="right">Telat</th>
                <th class="right">Potongan 8%</th>
                <th class="right">Alpha</th>
                <th class="right">Kasbon Tunai</th>
                <th class="right">Kasbon Non-Tunai</th>
                <th class="right">Pajak</th>
                <th class="right">Lain-Lain</th>
                <th class="right">BPJS Kes (Kr)</th>
                <th class="right">BPJS Kes (Pr)</th>
                <th class="right">BPJS Ket. Full (Kr)</th>
                <th class="right">BPJS Ket. Full (Pr)</th>
                <th class="right">BPJS Ket. Part. (Kr)</th>
                <th class="right">BPJS Ket. Part. (Pr)</th>
                <th class="right">Total Gaji</th>
                <th class="right">Iuran</th>
                <th class="right">Gaji Bersih</th>
                <th class="right">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalGajiPokok = 0; $totalTunjangan = 0; $totalLembur = 0;
                $totalUMakan = 0; $totalTelat = 0; $totalPenalty8 = 0; $totalAlpha = 0; $totalKasbonTunai = 0; $totalKasbonNontunai = 0; $totalPajak = 0; $totalLain = 0; $totalBPJSKKr = 0;
                $totalBPJSKPr = 0; $totalBPJSKetFullKr = 0; $totalBPJSKetFullPr = 0; $totalBPJSKetPartKr = 0; $totalBPJSKetPartPr = 0;
                $totalGajiKotor = 0; $totalIuran = 0; $totalGajiBersih = 0;
            @endphp
            @forelse($payrolls as $i => $p)
            @php
                $gajiKotor = $p->net_salary + $p->iuran_bulanan_deduction;
                $totalGajiPokok += $p->base_salary;
                $totalTunjangan += $p->allowance;
                $totalLembur += $p->overtime_pay;
                $totalUMakan += $p->uang_makan_lembur + $p->uang_makan_harian;
                $totalTelat += $p->late_penalty;
                $totalPenalty8 += $p->late_penalty_percent;
                $totalAlpha += $p->absent_penalty;
                $totalKasbonTunai += $p->cash_advance_tunai;
                $totalKasbonNontunai += $p->cash_advance_nontunai;
                $totalPajak += $p->tax_amount;
                $totalLain += $p->other_deductions;
                $totalBPJSKKr += $p->bpjs_kesehatan_deduction;
                $totalBPJSKPr += $p->bpjs_kesehatan_company;
                $isPartial = ($p->employee?->bpjs_ketenagakerjaan_type ?? null) === 'partial';
                if ($isPartial) {
                    $totalBPJSKetPartKr += $p->bpjs_ketenagakerjaan_deduction;
                    $totalBPJSKetPartPr += $p->bpjs_ketenagakerjaan_company;
                } else {
                    $totalBPJSKetFullKr += $p->bpjs_ketenagakerjaan_deduction;
                    $totalBPJSKetFullPr += $p->bpjs_ketenagakerjaan_company;
                }
                $totalGajiKotor += $gajiKotor;
                $totalIuran += $p->iuran_bulanan_deduction;
                $totalGajiBersih += $p->net_salary;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->employee->full_name ?? '-' }}</td>
                <td>{{ $p->employee->position->name ?? $p->employee->department->name ?? '-' }}</td>
                <td class="right"><span class="badge {{ ($p->employee_type ?? 'bulanan') === 'harian' ? 'badge-h' : 'badge-b' }}">{{ ($p->employee_type ?? 'bulanan') === 'harian' ? 'Harian' : 'Bulanan' }}</span></td>
                <td class="right">Rp {{ number_format($p->base_salary, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->allowance, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->uang_makan_lembur + $p->uang_makan_harian, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->late_penalty, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->late_penalty_percent, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->absent_penalty, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->cash_advance_tunai, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->cash_advance_nontunai, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->tax_amount, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->other_deductions, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->bpjs_kesehatan_deduction, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->bpjs_kesehatan_company, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($isPartial ? 0 : $p->bpjs_ketenagakerjaan_deduction, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($isPartial ? 0 : $p->bpjs_ketenagakerjaan_company, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($isPartial ? $p->bpjs_ketenagakerjaan_deduction : 0, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($isPartial ? $p->bpjs_ketenagakerjaan_company : 0, 0, ',', '.') }}</td>
                <td class="right" style="font-weight:600;">Rp {{ number_format($gajiKotor, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->iuran_bulanan_deduction, 0, ',', '.') }}</td>
                <td class="right" style="font-weight:600;">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</td>
                <td class="right">
                    @if($p->status == 'paid') Dibayar
                    @elseif($p->status == 'approved') Disetujui
                    @elseif($p->status == 'cancelled') Dibatalkan
                    @else Draft
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="25" style="text-align:center;padding:12px;color:#94a3b8;">Belum ada data</td></tr>
            @endforelse
            <tr class="totals">
                <td colspan="4" style="text-align:right;">Total</td>
                <td class="right">Rp {{ number_format($totalGajiPokok, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalTunjangan, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalLembur, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalUMakan, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalTelat, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalPenalty8, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalAlpha, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalKasbonTunai, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalKasbonNontunai, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalPajak, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalLain, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKKr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKPr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKetFullKr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKetFullPr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKetPartKr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKetPartPr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalGajiKotor, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalIuran, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalGajiBersih, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <script>window.print();</script>
</body>
</html>
