<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan BPJS - {{ request('period') ?? 'Semua Periode' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page { margin: 8mm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 9px;
            color: #1e293b;
        }
        h1 { font-size: 14px; margin-bottom: 2px; }
        .sub { font-size: 10px; color: #64748b; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 4px 6px;
            text-align: left;
            font-size: 8px;
            font-weight: 600;
            color: #fff;
            background: #1e293b;
            text-transform: uppercase;
        }
        td {
            padding: 3px 6px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) td { background: #f8fafc; }
        .text-right { text-align: right; }
        .totals td {
            font-weight: 700;
            background: #f1f5f9;
            border-top: 2px solid #1e293b;
            padding: 4px 6px;
        }
        .badge {
            font-size: 7px;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .sub-text { font-size: 7px; color: #94a3b8; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <h1>Laporan BPJS</h1>
    <p class="sub">Periode: {{ request('period') ?? 'Semua Periode' }} | {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:10%;">NIK</th>
                <th style="width:16%;">Nama</th>
                <th style="width:10%;">Dept</th>
                <th style="width:12%;">Jenis BPJS</th>
                <th style="width:15%;" class="text-right">Perusahaan</th>
                <th style="width:15%;" class="text-right">Karyawan</th>
                <th style="width:15%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; $grandTotalPerusahaan = 0; $grandTotalKaryawan = 0; $grandTotal = 0; $bpjsType = request('bpjs_type'); @endphp
            @forelse($payrolls as $p)
                @php
                    $hasKes = ($p->bpjs_kesehatan_deduction > 0 || $p->bpjs_kesehatan_company > 0);
                    $hasKet = ($p->bpjs_ketenagakerjaan_deduction > 0 || $p->bpjs_ketenagakerjaan_company > 0);
                    $showKes = $hasKes && (!$bpjsType || $bpjsType === 'kesehatan');
                    $showKet = $hasKet && (!$bpjsType || $bpjsType === 'ketenagakerjaan');
                @endphp
                @if($showKes)
                @php
                    $totalKes = $p->bpjs_kesehatan_company + $p->bpjs_kesehatan_deduction;
                    $grandTotalPerusahaan += $p->bpjs_kesehatan_company;
                    $grandTotalKaryawan += $p->bpjs_kesehatan_deduction;
                    $grandTotal += $totalKes;
                    $no++;
                @endphp
                <tr>
                    <td>{{ $no }}</td>
                    <td>{{ $p->identity_number ?? '-' }}</td>
                    <td>{{ $p->full_name }}</td>
                    <td>{{ $p->employee->department->name ?? '-' }}</td>
                    <td><span class="badge badge-purple">Kesehatan</span></td>
                    <td class="text-right">Rp {{ number_format($p->bpjs_kesehatan_company, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($p->bpjs_kesehatan_deduction, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalKes, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($showKet)
                @php
                    $totalKet = $p->bpjs_ketenagakerjaan_company + $p->bpjs_ketenagakerjaan_deduction;
                    $grandTotalPerusahaan += $p->bpjs_ketenagakerjaan_company;
                    $grandTotalKaryawan += $p->bpjs_ketenagakerjaan_deduction;
                    $grandTotal += $totalKet;
                    $no++;
                @endphp
                <tr>
                    <td>{{ $no }}</td>
                    <td>{{ $p->identity_number ?? '-' }}</td>
                    <td>{{ $p->full_name }}</td>
                    <td>{{ $p->employee->department->name ?? '-' }}</td>
                    <td><span class="badge badge-blue">Ketenagakerjaan</span>@if(($p->employee->bpjs_ketenagakerjaan_type ?? 'full') === 'partial') <span class="sub-text">(partial)</span>@endif</td>
                    <td class="text-right">Rp {{ number_format($p->bpjs_ketenagakerjaan_company, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($p->bpjs_ketenagakerjaan_deduction, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalKet, 0, ',', '.') }}</td>
                </tr>
                @endif
            @empty
            <tr><td colspan="8" style="text-align:center;padding:12px;color:#94a3b8;">Belum ada data</td></tr>
            @endforelse
            @if($no > 0)
            <tr class="totals">
                <td colspan="5" style="text-align:right;">Grand Total</td>
                <td class="text-right">Rp {{ number_format($grandTotalPerusahaan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalKaryawan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>
    @if(request('bpjs_print') !== 'false')
    <script>window.print();</script>
    @endif
</body>
</html>
