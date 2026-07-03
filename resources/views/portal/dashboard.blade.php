@extends('portal.layouts.app')

@section('content')
<div class="space-y-4">
    {{-- Profile Header --}}
    <div class="sticky top-0 z-30 -mx-4 px-4 pt-4 pb-3 bg-gray-50 dark:bg-gray-950">
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-700 dark:from-blue-800 dark:to-indigo-900 rounded-2xl p-5 shadow-lg">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative flex items-center gap-4">
            <div class="relative flex-shrink-0">
                <form id="photoForm" method="POST" action="{{ route('portal.photo.update') }}" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/jpg" onchange="document.getElementById('photoForm').submit()">
                </form>
                @if($employee->photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($employee->photo) }}" class="w-14 h-14 rounded-full object-cover ring-2 ring-white/30 cursor-pointer" onclick="document.getElementById('photoInput').click()" alt="Foto">
                @else
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-xl ring-2 ring-white/30 cursor-pointer" onclick="document.getElementById('photoInput').click()">
                    {{ substr($employee->full_name, 0, 1) }}
                </div>
                @endif
                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-sm cursor-pointer" onclick="document.getElementById('photoInput').click()">
                    <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-base font-bold text-white leading-tight">{{ $employee->full_name }}</h2>
                <p class="text-xs text-blue-200 truncate mt-0.5">{{ $employee->position->name ?? '-' }}</p>
            </div>
            <button @click="toggleDark()" class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center text-white hover:bg-white/25 transition-colors flex-shrink-0">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
        </div>
    </div>
</div>

    @if($isBirthday)
    <div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl shadow-sm p-4 flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-white">Selamat Ulang Tahun, {{ $employee->full_name }}!</p>
            <p class="text-xs text-white/80 mt-0.5">Semoga sehat dan sukses selalu</p>
        </div>
    </div>
    @endif

    @if($pendingHeadCount > 0)
    <a href="{{ route('portal.leave-approval.index') }}" class="block bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl shadow-sm p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-white">{{ $pendingHeadCount }} pengajuan cuti menunggu persetujuan</p>
                <p class="text-xs text-white/80 mt-0.5">Klik untuk menyetujui atau menolak</p>
            </div>
        </div>
    </a>
    @endif

    {{-- Period Selector --}}
    @if($payrolls->count() > 1)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-3">
        <form method="GET" action="{{ route('portal.dashboard') }}" class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <select name="period" onchange="this.form.submit()" class="flex-1 text-sm bg-transparent text-gray-900 dark:text-white border-none focus:ring-0 py-1 appearance-none">
                @foreach($payrolls as $p)
                <option value="{{ $p->period }}" {{ ($selectedPeriod ?: $payrolls->first()->period) == $p->period ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $p->period)->locale('id')->isoFormat('MMMM YYYY') }}
                </option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    {{-- Attendance Summary --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan Absensi</h3>
                @if($latestPayroll)
                <p class="text-[10px] text-gray-400">Periode {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</p>
                @endif
            </div>
            <span class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ $attendanceSummary['hadir'] + $attendanceSummary['terlambat'] + $attendanceSummary['sakit'] + $attendanceSummary['izin'] + $attendanceSummary['cuti'] + $attendanceSummary['alpha'] }} hari</span>
        </div>
        <div class="grid grid-cols-6 gap-1.5">
            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl text-center">
                <p class="text-base font-bold text-emerald-600 dark:text-emerald-400">{{ $attendanceSummary['hadir'] }}</p>
                <p class="text-[9px] text-gray-500 mt-0.5">Hadir</p>
            </div>
            <div class="p-2 bg-orange-50 dark:bg-orange-900/20 rounded-xl text-center">
                <p class="text-base font-bold text-orange-600 dark:text-orange-400">{{ $attendanceSummary['terlambat'] }}</p>
                <p class="text-[9px] text-gray-500 mt-0.5">Telat</p>
            </div>
            <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-xl text-center">
                <p class="text-base font-bold text-red-600 dark:text-red-400">{{ $attendanceSummary['alpha'] }}</p>
                <p class="text-[9px] text-gray-500 mt-0.5">Alpha</p>
            </div>
            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-center">
                <p class="text-base font-bold text-blue-600 dark:text-blue-400">{{ $attendanceSummary['sakit'] }}</p>
                <p class="text-[9px] text-gray-500 mt-0.5">Sakit</p>
            </div>
            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-xl text-center">
                <p class="text-base font-bold text-purple-600 dark:text-purple-400">{{ $attendanceSummary['izin'] }}</p>
                <p class="text-[9px] text-gray-500 mt-0.5">Izin</p>
            </div>
            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl text-center">
                <p class="text-base font-bold text-indigo-600 dark:text-indigo-400">{{ $attendanceSummary['cuti'] }}</p>
                <p class="text-[9px] text-gray-500 mt-0.5">Cuti</p>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('portal.leave.create') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 text-center hover:shadow-md transition-shadow active:scale-[0.97]">
            <div class="w-11 h-11 mx-auto mb-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-900 dark:text-white">Ajukan Cuti</p>
        </a>
        <a href="{{ route('portal.leave.create') }}?type=sakit" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 text-center hover:shadow-md transition-shadow active:scale-[0.97]">
            <div class="w-11 h-11 mx-auto mb-2 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-900 dark:text-white">Surat Sakit</p>
        </a>
        <a href="{{ route('portal.cash-advance.create') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 text-center hover:shadow-md transition-shadow active:scale-[0.97]">
            <div class="w-11 h-11 mx-auto mb-2 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-900 dark:text-white">Kasbon</p>
        </a>
        <a href="{{ route('portal.attendance.history') }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 text-center hover:shadow-md transition-shadow active:scale-[0.97]">
            <div class="w-11 h-11 mx-auto mb-2 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-900 dark:text-white">Riwayat Absensi</p>
        </a>
    </div>

    {{-- Latest Salary --}}
    @if($latestPayroll)
    @php
        $p = $latestPayroll;
        $totalIncome = $p->base_salary + $p->allowance + $p->bonus + $p->other_additions + $p->overtime_pay + $p->uang_makan_lembur + $p->uang_makan_harian;
        $totalDeductions = $p->late_penalty + $p->late_penalty_percent + $p->absent_penalty + $p->cash_advance_deduction + $p->bpjs_deduction + $p->tax_amount + $p->other_deductions;
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Slip Gaji Detail</h3>
            <span class="text-[10px] font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-lg">{{ \Carbon\Carbon::createFromFormat('Y-m', $p->period)->locale('id')->isoFormat('MMMM YYYY') }}</span>
        </div>
        <div class="space-y-3 text-sm">
            {{-- Income --}}
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Pendapatan</p>
                <div class="space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Gaji Pokok</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($p->base_salary, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tunjangan</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($p->allowance, 0, ',', '.') }}</span>
                    </div>
                    @if($p->overtime_pay > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Lembur</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->uang_makan_lembur > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Uang Makan Lembur</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($p->uang_makan_lembur, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->uang_makan_harian > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Uang Makan Harian</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($p->uang_makan_harian, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->bonus > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bonus</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($p->bonus, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->other_additions > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tambahan Lain-lain</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($p->other_additions, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-1.5 border-t border-gray-100 dark:border-gray-700">
                        <span class="font-semibold text-gray-900 dark:text-white">Total Pendapatan</span>
                        <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Deductions --}}
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Potongan</p>
                <div class="space-y-1.5">
                    @if($p->late_penalty > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Keterlambatan</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->late_penalty, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->late_penalty_percent > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Denda Telat 8%</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->late_penalty_percent, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->absent_penalty > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Alpha</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->absent_penalty, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->bpjs_kesehatan_deduction > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">BPJS Kesehatan</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->bpjs_kesehatan_deduction, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->bpjs_ketenagakerjaan_deduction > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">BPJS Ketenagakerjaan</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->bpjs_ketenagakerjaan_deduction, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->iuran_bulanan_deduction > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Iuran Bulanan</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->iuran_bulanan_deduction, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->tax_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">PPh 21</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->cash_advance_deduction > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Kasbon</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->cash_advance_deduction, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($p->other_deductions > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Potongan Lain-lain</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($p->other_deductions, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($totalDeductions > 0)
                    <div class="flex justify-between pt-1.5 border-t border-gray-100 dark:border-gray-700">
                        <span class="font-semibold text-gray-900 dark:text-white">Total Potongan</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($totalDeductions, 0, ',', '.') }}</span>
                    </div>
                    @else
                    <div class="flex justify-between">
                        <span class="text-gray-400 italic">Tidak ada potongan</span>
                    </div>
                    @endif
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-600">
            <div class="flex justify-between">
                <span class="font-bold text-gray-900 dark:text-white text-sm">Gaji Bersih</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-base">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</span>
            </div>

            @if($p->notes)
            <div class="mt-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                <p class="text-[10px] font-medium text-gray-400 mb-1">Catatan</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $p->notes }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Leave History --}}
    @if($recentLeaves->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Cuti</h3>
            <span class="text-[10px] text-gray-400">{{ $recentLeaves->count() }} terakhir</span>
        </div>
        <div class="space-y-1">
            @foreach($recentLeaves as $leave)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $leave->leaveType->name ?? '-' }}</p>
                        <p class="text-[11px] text-gray-400">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</p>
                    </div>
                </div>
                @if($leave->status == 'approved')
                <span class="text-[10px] font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-lg">Disetujui</span>
                @elseif($leave->status == 'rejected')
                <span class="text-[10px] font-medium text-red-600 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-lg">Ditolak</span>
                @else
                <span class="text-[10px] font-medium text-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 px-2 py-1 rounded-lg">Pending</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Leave & DP Balance --}}
    @if($leaveBalances->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Sisa Cuti</h3>
        <div class="space-y-3">
            @foreach($leaveBalances as $lb)
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-700 dark:text-gray-300">{{ $lb->name }}</span>
                    <span class="text-xs font-medium {{ $lb->remaining > 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ $lb->remaining }} / {{ $lb->max }} hari</span>
                </div>
                <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $lb->remaining > 0 ? 'bg-blue-500' : 'bg-red-400' }}" style="width: {{ $lb->max > 0 ? ($lb->remaining / $lb->max) * 100 : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Cash Advance History --}}
    @if($cashAdvances->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Kasbon</h3>
            <span class="text-[10px] text-gray-400">{{ $cashAdvances->count() }} terakhir</span>
        </div>
        <div class="space-y-1">
            @foreach($cashAdvances as $ca)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($ca->amount, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-gray-400">{{ $ca->installment_count }}x cicilan · Sisa Rp {{ number_format($ca->remaining_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if($ca->status == 'approved')
                <span class="text-[10px] font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-lg">Disetujui</span>
                @elseif($ca->status == 'rejected')
                <span class="text-[10px] font-medium text-red-600 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-lg">Ditolak</span>
                @elseif($ca->status == 'paid')
                <span class="text-[10px] font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded-lg">Lunas</span>
                @else
                <span class="text-[10px] font-medium text-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 px-2 py-1 rounded-lg">Pending</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Personal Info --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Data Diri</h3>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-700/50 text-sm">
            <div class="flex justify-between py-2.5">
                <span class="text-gray-500">NIK</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->nik ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2.5">
                <span class="text-gray-500">Departemen</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->department->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2.5">
                <span class="text-gray-500">Jabatan</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->position->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2.5">
                <span class="text-gray-500">Station</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->station->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2.5">
                <span class="text-gray-500">No. Hape</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->phone ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2.5">
                <span class="text-gray-500">No. KTP</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->identity_number ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2.5">
                <span class="text-gray-500">Tanggal Masuk</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->join_date ? $employee->join_date->format('d M Y') : '-' }}</span>
            </div>
            <div class="flex justify-between py-2.5">
                <span class="text-gray-500">Status</span>
                @php $s = $employee->employment_status ?? 'permanent'; @endphp
                <span class="font-medium @if($s === 'permanent') text-emerald-600 @elseif($s === 'contract_year') text-orange-600 @else text-purple-600 @endif">
                    @if($s === 'permanent') Tetap
                    @elseif($s === 'contract_year') Kontrak Tahunan
                    @else Kontrak Tetap
                    @endif
                </span>
            </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-50 dark:border-gray-700/50">
            <a href="{{ route('portal.password') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Ubah Password
            </a>
        </div>
    </div>
</div>
@endsection