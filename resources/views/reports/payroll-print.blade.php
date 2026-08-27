<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Payroll - {{ $period ?? 'Semua Periode' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page { margin: 8mm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 10px;
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
            background: #2563eb;
            text-transform: uppercase;
        }
        th:last-child, td:last-child { text-align: right; }
        td {
            padding: 3px 6px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) td { background: #f8fafc; }
        .totals td {
            font-weight: 700;
            font-size: 12px;
            color: #2563eb;
            background: #eff6ff;
            border-top: 2px solid #2563eb;
            padding: 5px 6px;
        }
        .badge {
            font-size: 8px;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: 600;
        }
        .badge-b { background: #dbeafe; color: #1d4ed8; }
        .badge-h { background: #ffedd5; color: #c2410c; }
        .bank-section h2 {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 2px solid #2563eb;
            text-transform: uppercase;
        }
        .page-break { page-break-before: always; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <h1>Laporan Payroll</h1>
    <p class="sub">Periode: {{ $period ?? 'Semua Periode' }}@if($stationName ?? null) | Station: {{ $stationName }}@endif | {{ now()->format('d/m/Y H:i') }}</p>
    @php
        $grouped = $payrolls->groupBy(fn($p) => $p->employee->bank_name ?? 'Cash (Tanpa Rekening)');
    @endphp
    @if($grouped->isEmpty())
    <table>
        <tbody>
            <tr><td colspan="8" style="text-align:center;padding:12px;color:#94a3b8;">Belum ada data</td></tr>
        </tbody>
    </table>
    @else
    @foreach($grouped as $bank => $items)
    <div class="bank-section {{ $loop->first ? '' : 'page-break' }}">
        <h2>{{ $bank }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:18%;">Nama</th>
                    <th style="width:18%;">No. KTP</th>
                    <th style="width:6%;">Jenis</th>
                    <th style="width:8%;">Bank</th>
                    <th style="width:14%;">No. Rekening</th>
                    <th style="width:16%;">Nama Rekening</th>
                    <th style="width:15%;">Gaji Bersih</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($items as $i => $p)
                @php $total += $p->net_salary; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->employee->full_name ?? '-' }}</td>
                    <td>{{ $p->employee->identity_number ?? '-' }}</td>
                    <td><span class="badge {{ ($p->employee_type ?? 'bulanan') === 'harian' ? 'badge-h' : 'badge-b' }}">{{ ($p->employee_type ?? 'bulanan') === 'harian' ? 'Harian' : 'Bulanan' }}</span></td>
                    <td>{{ $p->employee->bank_name ?? 'Cash' }}</td>
                    <td>{{ $p->employee->bank_account ?? '-' }}</td>
                    <td>{{ $p->employee->bank_holder ?? '-' }}</td>
                    <td style="font-weight:600;">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="totals">
                    <td colspan="7" style="text-align:right;">Total {{ $bank }}</td>
                    <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach
    @endif
    <script>window.print();</script>
</body>
</html>
