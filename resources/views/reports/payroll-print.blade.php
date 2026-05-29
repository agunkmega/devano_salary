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
            background: #1e293b;
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
            background: #f1f5f9;
            border-top: 2px solid #1e293b;
            padding: 4px 6px;
        }
        .badge {
            font-size: 8px;
            padding: 1px 5px;
            border-radius: 3px;
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
    <h1>Laporan Payroll</h1>
    <p class="sub">Periode: {{ $period ?? 'Semua Periode' }} | {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:18%;">Nama</th>
                <th style="width:18%;">Jabatan</th>
                <th style="width:6%;">Jenis</th>
                <th style="width:8%;">Bank</th>
                <th style="width:14%;">No. Rekening</th>
                <th style="width:16%;">Nama Rekening</th>
                <th style="width:15%;">Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($payrolls as $i => $p)
            @php $total += $p->net_salary; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->employee->full_name ?? '-' }}</td>
                <td>{{ $p->employee->position->name ?? $p->employee->department->name ?? '-' }}</td>
                <td><span class="badge {{ ($p->employee->employee_type ?? 'bulanan') === 'harian' ? 'badge-h' : 'badge-b' }}">{{ ($p->employee->employee_type ?? 'bulanan') === 'harian' ? 'Harian' : 'Bulanan' }}</span></td>
                <td>{{ $p->employee->bank_name ?? '-' }}</td>
                <td>{{ $p->employee->bank_account ?? '-' }}</td>
                <td>{{ $p->employee->bank_holder ?? '-' }}</td>
                <td style="font-weight:600;">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:12px;color:#94a3b8;">Belum ada data</td></tr>
            @endforelse
            <tr class="totals">
                <td colspan="7" style="text-align:right;">Total</td>
                <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <script>window.print();</script>
</body>
</html>
