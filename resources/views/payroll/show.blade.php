@extends('layouts.admin')

@section('page-title', 'Detail Payroll')
@section('page-subtitle')
    @php
        $perStart = \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->subMonth()->day(26);
        $perEnd = \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->day(25);
    @endphp
    {{ $perStart->format('d M') }} - {{ $perEnd->format('d M Y') }}
@endsection

@section('page-content')
@php
    $fmt = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $emp = $payroll->employee;
    $empType = $emp?->employee_type ?? 'bulanan';
    $statusMap = ['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Disetujui', 'paid' => 'Dibayar'];
@endphp
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xl font-bold">
                    {{ substr($emp->full_name ?? 'B', 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $emp->full_name ?? 'Unknown' }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $emp->nik ?? '-' }} | {{ $emp->position->name ?? '-' }} | {{ $emp->department->name ?? '-' }}</p>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $empType === 'harian' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">{{ $empType === 'harian' ? 'Harian' : 'Bulanan' }}</span>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
                <a href="{{ route('admin.payrolls.slip-pdf', $payroll->id) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </a>
                    <a href="#" onclick="event.preventDefault(); if(confirm('Kirim slip gaji ke WhatsApp {{ $emp->phone ?? '-' }}?')) document.getElementById('sendWaForm').submit();" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm ring-2 ring-emerald-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    <a href="#" onclick="event.preventDefault(); if(confirm('Kirim slip gaji ke email {{ $emp->email ?? '-' }}?')) document.getElementById('sendEmailForm').submit();" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-500 rounded-xl hover:bg-blue-600 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Email
                    </a>
                </div>
            </div>

            <form id="sendWaForm" method="POST" action="{{ route('admin.payrolls.send-whatsapp', $payroll->id) }}" style="display:none;">@csrf</form>
            <form id="sendEmailForm" method="POST" action="{{ route('admin.payrolls.send-email', $payroll->id) }}" style="display:none;">@csrf</form>

        @if($empType === 'harian' && $payroll->attendance_days !== null)
        <div class="mt-4 grid grid-cols-4 gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Rate Harian</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($emp->base_salary) }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Hadir</p>
                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ $payroll->attendance_days }} hari</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Cuti Dibayar</p>
                <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $payroll->paid_leave_days ?? 0 }} hari</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Absen</p>
                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $payroll->absent_days ?? 0 }} hari</p>
            </div>
        </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Pendapatan
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">Gaji Pokok @if($empType === 'harian' && $payroll->attendance_days !== null)<span class="text-xs text-gray-400"> ({{ $payroll->attendance_days }} hari)</span>@endif</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->base_salary) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">Tunjangan</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->allowance) }}</span>
            </div>
            @if($payroll->bonus > 0)
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">Bonus</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->bonus) }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">Lembur</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->overtime_pay) }}</span>
            </div>
            @if($payroll->uang_makan_lembur > 0)
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">Uang Makan Lembur</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->uang_makan_lembur) }}</span>
            </div>
            @endif
            @if($payroll->uang_makan_harian > 0)
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">Uang Makan Harian</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->uang_makan_harian) }}</span>
            </div>
            @endif
            @if($payroll->other_additions > 0)
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">Tambahan Lain-lain</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->other_additions) }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center py-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Subtotal Pendapatan</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $fmt($payroll->base_salary + $payroll->allowance + $payroll->bonus + $payroll->other_additions + $payroll->overtime_pay + $payroll->uang_makan_lembur + $payroll->uang_makan_harian) }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Potongan Absensi
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Terlambat</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->late_penalty) }}</span>
                </div>
                @if($payroll->late_penalty_percent > 0)
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Terlambat 8%</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->late_penalty_percent) }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Alpha</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->absent_penalty) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Pot. Absensi</span>
                    <span class="text-sm font-bold text-red-600 dark:text-red-400">{{ $fmt($payroll->late_penalty + $payroll->late_penalty_percent + $payroll->absent_penalty) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                Potongan Wajib
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">BPJS</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->bpjs_deduction) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Kasbon</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->cash_advance_deduction) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">PPh 21</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->tax_amount) }}</span>
                </div>
                @if($payroll->other_deductions > 0)
                <div class="flex justify-between items-center py-2 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Potongan Lain-lain</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->other_deductions) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($payroll->notes)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            Catatan
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $payroll->notes }}</p>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between py-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Gaji Bersih</p>
                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $fmt($payroll->net_salary) }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <span class="text-sm font-medium px-3 py-1.5 rounded-full @switch($payroll->status)
                    @case('draft') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 @break
                    @case('pending') bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 @break
                    @case('approved') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 @break
                    @case('paid') bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 @break
                    @default bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400
                @endswitch">{{ $statusMap[$payroll->status] ?? ucfirst($payroll->status) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection