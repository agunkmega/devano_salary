<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $payroll->employee->full_name ?? 'Karyawan' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 0;
            size: 105mm 148mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 7px;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .page {
            width: 105mm;
            min-height: 148mm;
            padding: 4mm 6mm;
            margin: 0 auto;
            background: #ffffff;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 4px;
            border-bottom: 1px solid #0f172a;
            margin-bottom: 5px;
        }
        .top-bar .brand h1 {
            font-size: 8px;
            font-weight: 700;
            color: #0f172a;
        }
        .top-bar .brand p {
            font-size: 5.5px;
            color: #64748b;
        }
        .top-bar .title {
            font-size: 6.5px;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .employee-info {
            padding: 4px 6px;
            background: #f8fafc;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        .employee-info .row {
            display: flex;
            padding: 1px 0;
        }
        .employee-info .row .lbl {
            min-width: 42px;
            color: #94a3b8;
            font-size: 6px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .employee-info .row .val {
            color: #1e293b;
            font-weight: 600;
            font-size: 7px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        thead th {
            padding: 1px 4px;
            text-align: left;
            font-size: 5.5px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid #e2e8f0;
        }
        thead th:last-child { text-align: right; }
        tbody td {
            padding: 1px 4px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 7px;
            color: #334155;
        }
        tbody td:last-child { text-align: right; white-space: nowrap; }
        .total td {
            padding: 2px 4px;
            font-weight: 700;
            font-size: 7px;
            color: #0f172a;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .sections { margin-bottom: 4px; }
        .sections table { margin-bottom: 5px; }
        .sections td:first-child { width: 70%; }
        .sections td:last-child { width: 30%; text-align: right; white-space: nowrap; }

        .summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .summary .lbl {
            font-size: 6px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary .amount {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #e2e8f0;
        }
        .footer .sign {
            text-align: center;
            flex: 1;
        }
        .footer .sign .lbl {
            font-size: 5.5px;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        .footer .sign .name {
            font-weight: 600;
            font-size: 6.5px;
            color: #1e293b;
            padding-top: 12px;
            border-top: 1px solid #cbd5e1;
            display: inline-block;
            min-width: 55px;
        }

        .print-btn { text-align: center; margin-top: 6px; }
        .print-btn button {
            background: #0f172a; color: white; border: none;
            padding: 3px 10px; border-radius: 3px;
            cursor: pointer; font-size: 7px;
            font-family: 'Inter', sans-serif;
        }
        @media print {
            .print-btn { display: none; }
            @page { margin: 0; size: 105mm 148mm; }
            .page { padding: 4mm 6mm; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 9px;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .page {
            width: 105mm;
            min-height: 297mm;
            padding: 6mm;
            margin: 0 auto;
            background: #ffffff;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 8px;
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 12px;
        }
        .top-bar .brand h1 {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.3px;
        }
        .top-bar .brand p {
            font-size: 7px;
            color: #64748b;
            margin-top: 1px;
        }
        .top-bar .title {
            font-size: 9px;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .employee-info {
            display: flex;
            gap: 16px;
            padding: 8px 10px;
            background: #f8fafc;
            border-radius: 6px;
            margin-bottom: 12px;
        }
        .employee-info .col { flex: 1; }
        .employee-info .row {
            display: flex;
            padding: 2px 0;
        }
        .employee-info .row .lbl {
            min-width: 56px;
            color: #94a3b8;
            font-size: 7.5px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .employee-info .row .val {
            color: #1e293b;
            font-weight: 600;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        thead th {
            padding: 4px 8px;
            text-align: left;
            font-size: 7px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #e2e8f0;
        }
        thead th:last-child { text-align: right; }
        tbody td {
            padding: 3px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 9px;
            color: #334155;
        }
        tbody td:last-child { text-align: right; font-weight: 500; }
        .total td {
            padding: 4px 8px;
            font-weight: 700;
            font-size: 9px;
            color: #0f172a;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .total td:last-child { font-weight: 700; }

        .sections {
            display: block;
            margin-bottom: 10px;
        }
        .sections > div { width: 100%; }
        .sections table { margin-bottom: 8px; }

        .summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-top: 2px;
        }
        .summary .lbl {
            font-size: 7.5px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .summary .amount {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        .footer .sign {
            text-align: center;
            flex: 1;
        }
        .footer .sign .lbl {
            font-size: 7px;
            color: #94a3b8;
            margin-bottom: 24px;
        }
        .footer .sign .name {
            font-weight: 600;
            font-size: 8.5px;
            color: #1e293b;
            padding-top: 20px;
            border-top: 1px solid #cbd5e1;
            display: inline-block;
            min-width: 80px;
        }

        .print-btn {
            text-align: center;
            margin-top: 12px;
        }
        .print-btn button {
            background: #0f172a;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
            font-family: 'Inter', sans-serif;
        }
        @media print {
            .print-btn { display: none; }
            @page { margin: 0; size: 105mm 297mm; }
            .page { padding: 6mm; }
        }
    </style>
</head>
<body>
    @php
        $fmt = fn($v) => 'Rp' . number_format((float) $v, 0, ',', '.');
        $emp = $payroll->employee;
        $periodLabel = \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->locale('id')->translatedFormat('F Y');
        $totalPendapatan = $payroll->base_salary + $payroll->allowance + $payroll->bonus + $payroll->overtime_pay + $payroll->uang_makan_lembur + $payroll->uang_makan_harian;
        $totalPotongan = $payroll->late_penalty + $payroll->absent_penalty + $payroll->cash_advance_deduction + $payroll->bpjs_deduction + $payroll->tax_amount;
    @endphp
    <div class="page">
        <div class="top-bar">
            <div class="brand">
                <h1>PT. DEVANO SILVER INDONESIA</h1>
                <p>Perumahan Safira Regency Blok C No. 12, Kediri</p>
            </div>
            <div class="title">Slip Gaji</div>
        </div>

        <div class="employee-info">
            <div class="row"><span class="lbl">NIK</span><span class="val">{{ $emp->nik ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Nama</span><span class="val">{{ $emp->full_name ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Jabatan</span><span class="val">{{ $emp->position->name ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Periode</span><span class="val">{{ $periodLabel }}</span></div>
        </div>

        <div class="sections">
            <table>
                <thead><tr><th>Pendapatan</th><th>Jumlah</th></tr></thead>
                <tbody>
                    <tr>
                        <td>Gaji Pokok
                            @if($payroll->employee->employee_type === 'harian' && $payroll->attendance_days !== null)
                            <span style="color:#94a3b8;font-size:6px;"> ({{ $payroll->attendance_days }} hr)</span>
                            @endif
                        </td>
                        <td>{{ $fmt($payroll->base_salary) }}</td>
                    </tr>
                    <tr><td>Tunjangan</td><td>{{ $fmt($payroll->allowance) }}</td></tr>
                    @if($payroll->bonus > 0)
                    <tr><td>Bonus</td><td>{{ $fmt($payroll->bonus) }}</td></tr>
                    @endif
                    <tr><td>Lembur</td><td>{{ $fmt($payroll->overtime_pay) }}</td></tr>
                    @if($payroll->uang_makan_lembur > 0)
                    <tr><td>Uang Makan Lembur</td><td>{{ $fmt($payroll->uang_makan_lembur) }}</td></tr>
                    @endif
                    @if($payroll->uang_makan_harian > 0)
                    <tr><td>Uang Makan Harian</td><td>{{ $fmt($payroll->uang_makan_harian) }}</td></tr>
                    @endif
                    <tr class="total"><td>Total Pendapatan</td><td>{{ $fmt($totalPendapatan) }}</td></tr>
                </tbody>
            </table>
            <table>
                <thead><tr><th>Potongan</th><th>Jumlah</th></tr></thead>
                <tbody>
                    <tr><td>Terlambat</td><td>{{ $fmt($payroll->late_penalty) }}</td></tr>
                    @if($payroll->absent_penalty > 0)
                    <tr><td>Alpha</td><td>{{ $fmt($payroll->absent_penalty) }}</td></tr>
                    @endif
                    @if($payroll->cash_advance_deduction > 0)
                    <tr><td>Cicilan Kasbon</td><td>{{ $fmt($payroll->cash_advance_deduction) }}</td></tr>
                    @endif
                    <tr><td>BPJS</td><td>{{ $fmt($payroll->bpjs_deduction) }}</td></tr>
                    @if($payroll->tax_amount > 0)
                    <tr><td>PPh 21</td><td>{{ $fmt($payroll->tax_amount) }}</td></tr>
                    @endif
                    @if(($payroll->paid_leave_days ?? 0) > 0)
                    <tr><td style="color:#3b82f6;">Cuti Dibayar ({{ $payroll->paid_leave_days }} hr)</td><td style="color:#3b82f6;">0</td></tr>
                    @endif
                    <tr class="total"><td>Total Potongan</td><td>{{ $fmt($totalPotongan) }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="summary">
            <div>
                <div class="lbl">Gaji Bersih</div>
                <div class="amount">{{ $fmt($payroll->net_salary) }}</div>
            </div>
            <div style="text-align:right;">
                <div class="lbl">Status</div>
                <div style="font-size:10px;font-weight:600;color:#64748b;margin-top:2px;">
                    @switch($payroll->status)
                        @case('draft') Draft @break
                        @case('pending') Pending @break
                        @case('approved') Disetujui @break
                        @case('paid') Dibayar @break
                        @default {{ ucfirst($payroll->status) }}
                    @endswitch
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="sign">
                <div class="lbl">Dibuat Oleh</div>
                <div class="name">( Finance )</div>
            </div>
            <div class="sign">
                <div class="lbl">Mengetahui</div>
                <div class="name">( HRD )</div>
            </div>
            <div class="sign">
                <div class="lbl">Menerima</div>
                <div class="name">( {{ $emp->full_name ?? 'Karyawan' }} )</div>
            </div>
        </div>

        <div class="print-btn">
            <button onclick="window.print()">Cetak Slip Gaji</button>
        </div>
    </div>
</body>
</html>
