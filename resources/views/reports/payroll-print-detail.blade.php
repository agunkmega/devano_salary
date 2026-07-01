<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Payroll Detail - {{ $period ?? 'Semua Periode' }}</title>
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
            background: #1e293b;
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
            background: #f1f5f9;
            border-top: 2px solid #1e293b;
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
    <h1>Laporan Payroll Detail</h1>
    <p class="sub">Periode: {{ $period ?? 'Semua Periode' }} | {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th class="right">Jenis</th>
                <th>Bank</th>
                <th>No. Rek</th>
                <th>Nama Rek</th>
                <th class="right">Gaji Pokok</th>
                <th class="right">Tunjangan</th>
                <th class="right">Lembur</th>
                <th class="right">U.Makan</th>
                <th class="right">Potongan</th>
                <th class="right">BPJS Kes (Kr)</th>
                <th class="right">BPJS Kes (Pr)</th>
                <th class="right">BPJS Ket (Kr)</th>
                <th class="right">BPJS Ket (Pr)</th>
                <th class="right">Total Gaji</th>
                <th class="right">Iuran</th>
                <th class="right">Gaji Bersih</th>
                <th class="right">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalGajiPokok = 0; $totalTunjangan = 0; $totalLembur = 0;
                $totalUMakan = 0; $totalPotongan = 0; $totalBPJSKKr = 0;
                $totalBPJSKPr = 0; $totalBPJSKetKr = 0; $totalBPJSKetPr = 0;
                $totalGajiKotor = 0; $totalIuran = 0; $totalGajiBersih = 0;
            @endphp
            @forelse($payrolls as $i => $p)
            @php
                $gajiKotor = $p->net_salary + $p->iuran_bulanan_deduction;
                $totalGajiPokok += $p->base_salary;
                $totalTunjangan += $p->allowance;
                $totalLembur += $p->overtime_pay;
                $totalUMakan += $p->uang_makan_lembur + $p->uang_makan_harian;
                $totalPotongan += $p->total_deductions;
                $totalBPJSKKr += $p->bpjs_kesehatan_deduction;
                $totalBPJSKPr += $p->bpjs_kesehatan_company;
                $totalBPJSKetKr += $p->bpjs_ketenagakerjaan_deduction;
                $totalBPJSKetPr += $p->bpjs_ketenagakerjaan_company;
                $totalGajiKotor += $gajiKotor;
                $totalIuran += $p->iuran_bulanan_deduction;
                $totalGajiBersih += $p->net_salary;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->employee->full_name ?? '-' }}</td>
                <td>{{ $p->employee->position->name ?? $p->employee->department->name ?? '-' }}</td>
                <td class="right"><span class="badge {{ ($p->employee->employee_type ?? 'bulanan') === 'harian' ? 'badge-h' : 'badge-b' }}">{{ ($p->employee->employee_type ?? 'bulanan') === 'harian' ? 'Harian' : 'Bulanan' }}</span></td>
                <td>{{ $p->employee->bank_name ?? '-' }}</td>
                <td>{{ $p->employee->bank_account ?? '-' }}</td>
                <td>{{ $p->employee->bank_holder ?? '-' }}</td>
                <td class="right">Rp {{ number_format($p->base_salary, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->allowance, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->uang_makan_lembur + $p->uang_makan_harian, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->total_deductions, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->bpjs_kesehatan_deduction, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->bpjs_kesehatan_company, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->bpjs_ketenagakerjaan_deduction, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($p->bpjs_ketenagakerjaan_company, 0, ',', '.') }}</td>
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
            <tr><td colspan="20" style="text-align:center;padding:12px;color:#94a3b8;">Belum ada data</td></tr>
            @endforelse
            <tr class="totals">
                <td colspan="7" style="text-align:right;">Total</td>
                <td class="right">Rp {{ number_format($totalGajiPokok, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalTunjangan, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalLembur, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalUMakan, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKKr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKPr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKetKr, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($totalBPJSKetPr, 0, ',', '.') }}</td>
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
