<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $payroll->employee->full_name ?? 'Karyawan' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 0;
            size: 105mm 148mm;
        }
        * { margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            font-size: 6.5px;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            line-height: 1.3;
        }
        .page {
            padding: 3mm 3mm;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 3px;
            border-bottom: 2px solid #1e293b;
            margin-bottom: 3px;
        }
        .header .brand h1 {
            font-size: 7.5px;
            font-weight: 800;
            color: #1e293b;
        }
        .header .brand p {
            font-size: 4.5px;
            color: #64748b;
        }
        .header .badge {
            font-size: 5.5px;
            font-weight: 700;
            color: #fff;
            background: #1e293b;
            padding: 1.5px 4px;
            border-radius: 2px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 3px;
        }
        .info-grid table { width: 100%; border-collapse: collapse; }
        .info-grid td {
            padding: 0.5px 2px;
            font-size: 5.5px;
        }
        .info-grid td.lbl {
            color: #94a3b8;
            font-weight: 600;
            width: 28px;
        }
        .info-grid td.val {
            color: #1e293b;
            font-weight: 600;
            font-size: 6px;
        }

        .tbl { width: 100%; border-collapse: collapse; margin-bottom: 1.5px; }
        .tbl col.lbl { width: 75%; }
        .tbl col.nom { width: 25%; }
        .tbl thead th {
            padding: 1px 3px;
            text-align: left;
            font-size: 4.5px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }
        .tbl thead th:last-child { text-align: right; }
        .tbl tbody td {
            padding: 1px 3px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 6px;
            color: #334155;
        }
        .tbl tbody td:last-child { text-align: right; }
        .tbl tbody tr.sub td { color: #94a3b8; font-size: 5px; }
        .tbl tbody tr.highlight td {
            font-weight: 700;
            color: #0f172a;
            border-top: 1px solid #94a3b8;
            border-bottom: 1px solid #94a3b8;
            background: #f1f5f9;
            padding: 1.5px 3px;
        }

        .net-wrap {
            width: 100%;
            padding: 2px 5px;
            background: #1e293b;
            margin: 2px 0 1.5px;
        }
        .net-wrap table { width: 100%; border-collapse: collapse; }
        .net-wrap td.lbl {
            font-size: 5px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .net-wrap td.nom {
            text-align: right;
            font-size: 9px;
            font-weight: 800;
            color: #fff;
        }

        .meta {
            font-size: 5px;
            color: #94a3b8;
            margin-bottom: 1.5px;
        }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td:last-child { text-align: right; }

        .signatures {
            width: 100%;
            margin-top: 3px;
            padding-top: 2px;
            border-top: 1px solid #e2e8f0;
        }
        .signatures table { width: 100%; border-collapse: collapse; }
        .signatures td {
            text-align: center;
            width: 33.33%;
        }
        .signatures td .lbl {
            font-size: 4.5px;
            color: #94a3b8;
            display: block;
            margin-bottom: 8px;
        }
        .signatures td .name {
            font-weight: 600;
            font-size: 5.5px;
            color: #1e293b;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            display: inline-block;
            min-width: 40px;
        }

        .print-btn { text-align: center; margin-top: 3px; }
        .print-btn button {
            background: #1e293b; color: #fff; border: none;
            padding: 2px 8px; border-radius: 2px; cursor: pointer;
            font-size: 5.5px; font-family: 'Inter', sans-serif;
        }
        @media print {
            .print-btn { display: none; }
            @page { margin: 0; size: 105mm 148mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        $fmt = fn($v) => 'Rp' . number_format((float) $v, 0, ',', '.');
        $emp = $payroll->employee;
        $periodLabel = \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->locale('id')->translatedFormat('F Y');
        $totalIncome = $payroll->base_salary + $payroll->allowance + $payroll->bonus + $payroll->overtime_pay + $payroll->uang_makan_lembur + $payroll->uang_makan_harian;
        $totalDeductions = $payroll->late_penalty + $payroll->absent_penalty + $payroll->cash_advance_deduction + $payroll->bpjs_deduction + $payroll->tax_amount;
        $showPaidLeave = ($payroll->paid_leave_days ?? 0) > 0;
    @endphp
    <div class="page">
        <div class="header">
            <div class="brand">
                <h1>{{ $companyName ?? 'PT. DEVANO SILVER INDONESIA' }}</h1>
                @if($companyAddress)
                <p>{{ $companyAddress }}</p>
                @endif
            </div>
            <div class="badge">SLIP GAJI</div>
        </div>

        <div class="info-grid">
            <table>
                <tr>
                    <td class="lbl">NIK</td>
                    <td class="val">{{ $emp->nik ?? '-' }}</td>
                    <td class="lbl">Periode</td>
                    <td class="val">{{ $periodLabel }}</td>
                </tr>
                <tr>
                    <td class="lbl">Nama</td>
                    <td class="val">{{ $emp->full_name ?? '-' }}</td>
                    <td class="lbl">Status</td>
                    <td class="val">{{ $emp->employee_type === 'bulanan' ? 'Bulanan' : 'Harian' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Jabatan</td>
                    <td class="val" colspan="3">{{ $emp->position->name ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <table class="tbl">
            <colgroup><col class="lbl"><col class="nom"></colgroup>
            <thead><tr><th>Pendapatan</th><th>Jumlah</th></tr></thead>
            <tbody>
                <tr><td>Gaji Pokok</td><td>{{ $fmt($payroll->base_salary) }}</td></tr>
                <tr><td>Tunjangan</td><td>{{ $fmt($payroll->allowance) }}</td></tr>
                @if($payroll->attendance_days !== null)
                <tr class="sub"><td>&nbsp;&#8594; {{ $emp->employee_type === 'harian' ? 'Hadir' : 'Hari Efektif' }}: {{ $payroll->attendance_days }} hr</td><td></td></tr>
                @endif
                @if($showPaidLeave)
                <tr class="sub"><td>&nbsp;&#8594; Cuti: {{ $payroll->paid_leave_days }} hr</td><td></td></tr>
                @endif
                @if($payroll->overtime_pay > 0)
                <tr><td>Lembur</td><td>{{ $fmt($payroll->overtime_pay) }}</td></tr>
                @endif
                @if($payroll->uang_makan_lembur > 0)
                <tr><td>Uang Makan Lembur</td><td>{{ $fmt($payroll->uang_makan_lembur) }}</td></tr>
                @endif
                @if($payroll->uang_makan_harian > 0)
                <tr><td>Uang Makan Harian</td><td>{{ $fmt($payroll->uang_makan_harian) }}</td></tr>
                @endif
                @if($payroll->bonus > 0)
                <tr><td>Bonus</td><td>{{ $fmt($payroll->bonus) }}</td></tr>
                @endif
                <tr class="highlight"><td>Total Pendapatan</td><td>{{ $fmt($totalIncome) }}</td></tr>
            </tbody>
        </table>

        <table class="tbl">
            <colgroup><col class="lbl"><col class="nom"></colgroup>
            <thead><tr><th>Potongan</th><th>Jumlah</th></tr></thead>
            <tbody>
                @if($payroll->late_penalty > 0)
                <tr><td>Keterlambatan</td><td>{{ $fmt($payroll->late_penalty) }}</td></tr>
                @endif
                @if($payroll->absent_penalty > 0)
                <tr><td>Alpha</td><td>{{ $fmt($payroll->absent_penalty) }}</td></tr>
                @endif
                @if($payroll->bpjs_deduction > 0)
                <tr><td>BPJS</td><td>{{ $fmt($payroll->bpjs_deduction) }}</td></tr>
                @endif
                @if($payroll->tax_amount > 0)
                <tr><td>PPh 21</td><td>{{ $fmt($payroll->tax_amount) }}</td></tr>
                @endif
                @if($payroll->cash_advance_deduction > 0)
                <tr><td>Kasbon</td><td>{{ $fmt($payroll->cash_advance_deduction) }}</td></tr>
                @endif
                @if($totalDeductions > 0)
                <tr class="highlight"><td>Total Potongan</td><td>{{ $fmt($totalDeductions) }}</td></tr>
                @else
                <tr class="sub"><td colspan="2" style="text-align:center;padding:2px;">Tidak ada potongan</td></tr>
                @endif
            </tbody>
        </table>

        <div class="net-wrap">
            <table>
                <tr>
                    <td class="lbl">Gaji Bersih</td>
                    <td class="nom">{{ $fmt($payroll->net_salary) }}</td>
                </tr>
            </table>
        </div>

        <div class="meta">
            <table>
                <tr>
                    <td>Status: 
                        @switch($payroll->status)
                            @case('draft') Draft @break
                            @case('pending') Pending @break
                            @case('approved') Disetujui @break
                            @case('paid') Dibayar @break
                            @default {{ ucfirst($payroll->status) }}
                        @endswitch
                    </td>
                    <td>Tgl Cetak: {{ now()->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>

        <div class="signatures">
            <table>
                <tr>
                    <td><span class="lbl">Dibuat Oleh</span><span class="name">( Finance )</span></td>
                    <td><span class="lbl">Mengetahui</span><span class="name">( HRD )</span></td>
                    <td><span class="lbl">Menerima</span><span class="name">( {{ $emp->full_name ?? 'Karyawan' }} )</span></td>
                </tr>
            </table>
        </div>

        <div class="print-btn">
            <button onclick="window.print()">Cetak Slip Gaji</button>
        </div>
    </div>
</body>
</html>
