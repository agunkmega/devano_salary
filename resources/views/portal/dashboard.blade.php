@extends('portal.layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-lg">
            {{ substr($employee->full_name, 0, 1) }}
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee->full_name }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->position->name ?? '-' }} · {{ $employee->department->name ?? '-' }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Ringkasan Absensi Bulan Ini</h3>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $attendanceSummary['hadir'] + $attendanceSummary['terlambat'] }}</p>
                <p class="text-[10px] text-gray-500">Hadir</p>
            </div>
            <div class="p-2 bg-orange-50 dark:bg-orange-900/20 rounded-xl">
                <p class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ $attendanceSummary['terlambat'] }}</p>
                <p class="text-[10px] text-gray-500">Telat</p>
            </div>
            <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ $attendanceSummary['alpha'] }}</p>
                <p class="text-[10px] text-gray-500">Alpha</p>
            </div>
            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $attendanceSummary['sakit'] }}</p>
                <p class="text-[10px] text-gray-500">Sakit</p>
            </div>
            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $attendanceSummary['izin'] }}</p>
                <p class="text-[10px] text-gray-500">Izin</p>
            </div>
            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $attendanceSummary['cuti'] }}</p>
                <p class="text-[10px] text-gray-500">Cuti</p>
            </div>
        </div>
    </div>

    @if($latestPayroll)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Gaji Terakhir</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Periode</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $latestPayroll->period }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Gaji Pokok</span>
                <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($latestPayroll->base_salary, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tunjangan</span>
                <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($latestPayroll->allowance, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Lembur</span>
                <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($latestPayroll->overtime_pay, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Potongan</span>
                <span class="font-medium text-red-600">Rp {{ number_format($latestPayroll->total_deductions, 0, ',', '.') }}</span>
            </div>
            <hr class="border-gray-200 dark:border-gray-700">
            <div class="flex justify-between text-base">
                <span class="font-semibold text-gray-900 dark:text-white">Gaji Bersih</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($latestPayroll->net_salary, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-3 gap-3">
        <a href="{{ route('portal.leave.create') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="w-10 h-10 mx-auto mb-2 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-xs font-medium text-gray-900 dark:text-white">Ajukan Cuti</p>
        </a>
        <a href="{{ route('portal.cash-advance.create') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="w-10 h-10 mx-auto mb-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-medium text-gray-900 dark:text-white">Kasbon</p>
        </a>
        <a href="{{ route('portal.leave.create') }}?type=sakit" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="w-10 h-10 mx-auto mb-2 bg-red-100 dark:bg-red-900/50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-xs font-medium text-gray-900 dark:text-white">Surat Sakit</p>
        </a>
    </div>

    @if($recentLeaves->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Riwayat Cuti</h3>
        <div class="space-y-2">
            @foreach($recentLeaves as $leave)
            <div class="flex items-center justify-between text-sm">
                <div>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $leave->leaveType->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</p>
                </div>
                @if($leave->status == 'approved')
                <span class="text-xs text-emerald-600 bg-emerald-100 dark:bg-emerald-900/50 px-2 py-0.5 rounded-lg">Disetujui</span>
                @elseif($leave->status == 'rejected')
                <span class="text-xs text-red-600 bg-red-100 dark:bg-red-900/50 px-2 py-0.5 rounded-lg">Ditolak</span>
                @else
                <span class="text-xs text-yellow-600 bg-yellow-100 dark:bg-yellow-900/50 px-2 py-0.5 rounded-lg">Pending</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($cashAdvances->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Riwayat Kasbon</h3>
        <div class="space-y-2">
            @foreach($cashAdvances as $ca)
            <div class="flex items-center justify-between text-sm">
                <div>
                    <p class="text-gray-900 dark:text-white font-medium">Rp {{ number_format($ca->amount, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500">{{ $ca->installment_count }}x cicilan · Sisa Rp {{ number_format($ca->remaining_amount, 0, ',', '.') }}</p>
                </div>
                @if($ca->status == 'approved')
                <span class="text-xs text-emerald-600 bg-emerald-100 dark:bg-emerald-900/50 px-2 py-0.5 rounded-lg">Disetujui</span>
                @elseif($ca->status == 'rejected')
                <span class="text-xs text-red-600 bg-red-100 dark:bg-red-900/50 px-2 py-0.5 rounded-lg">Ditolak</span>
                @elseif($ca->status == 'paid')
                <span class="text-xs text-blue-600 bg-blue-100 dark:bg-blue-900/50 px-2 py-0.5 rounded-lg">Lunas</span>
                @else
                <span class="text-xs text-yellow-600 bg-yellow-100 dark:bg-yellow-900/50 px-2 py-0.5 rounded-lg">Pending</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Data Diri</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">NIK</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->nik ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Departemen</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->department->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Jabatan</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->position->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">No. Hape</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->phone ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Email</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->email ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tanggal Masuk</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $employee->join_date ? $employee->join_date->format('d M Y') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
