@extends('layouts.admin')

@section('page-title', 'Detail Payroll')
@section('page-subtitle', 'Periode ' . (\Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->locale('id')->translatedFormat('F Y') ?? $payroll->period))

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
            </div>
        </div>

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
            <div class="flex justify-between items-center py-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Subtotal Pendapatan</span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $fmt($payroll->base_salary + $payroll->allowance + $payroll->bonus + $payroll->overtime_pay + $payroll->uang_makan_lembur + $payroll->uang_makan_harian) }}</span>
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
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Alpha</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->absent_penalty) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Pot. Absensi</span>
                    <span class="text-sm font-bold text-red-600 dark:text-red-400">{{ $fmt($payroll->late_penalty + $payroll->absent_penalty) }}</span>
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
            </div>
        </div>
    </div>

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