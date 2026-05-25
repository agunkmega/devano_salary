@extends('layouts.admin')

@section('page-title', 'Detail Pegawai')
@section('page-subtitle', $employee->full_name ?? '')

@section('page-content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="w-20 h-20 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-2xl font-bold flex-shrink-0">
                {{ substr($employee->full_name ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee->full_name ?? '-' }}</h2>
                <p class="text-gray-500 dark:text-gray-400">{{ $employee->nik ?? '-' }}</p>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $employee->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                        {{ $employee->is_active ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->department->name ?? '-' }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">|</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->position->name ?? '-' }}</span>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $employee->employee_type == 'bulanan' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' }}">
                        {{ $employee->employee_type == 'bulanan' ? 'Bulanan' : 'Harian' }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.employees.edit', $employee->id) }}" class="px-4 py-2 text-sm font-medium text-orange-600 bg-orange-50 dark:bg-orange-900/20 rounded-xl hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Pribadi</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->email ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Telepon</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->phone ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Lahir</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->birth_date?->format('d M Y') ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Kelamin</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->gender === 'L' ? 'Laki-laki' : ($employee->gender === 'P' ? 'Perempuan' : '-') }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Agama</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->religion ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alamat</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->address ?? '-' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Pekerjaan</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Departemen</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->department->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jabatan</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->position->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Shift</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->shift->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Masuk</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->join_date?->format('d M Y') ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Masa Kerja</dt><dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->join_date ? $employee->join_date->diffInMonths(now()) . ' bulan' : '-' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ringkasan Kehadiran (Bulan Ini)</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20"><p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">18</p><p class="text-xs text-gray-500 dark:text-gray-400">Hadir</p></div>
                    <div class="text-center p-4 rounded-xl bg-orange-50 dark:bg-orange-900/20"><p class="text-2xl font-bold text-orange-600 dark:text-orange-400">2</p><p class="text-xs text-gray-500 dark:text-gray-400">Terlambat</p></div>
                    <div class="text-center p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20"><p class="text-2xl font-bold text-blue-600 dark:text-blue-400">1</p><p class="text-xs text-gray-500 dark:text-gray-400">Izin</p></div>
                    <div class="text-center p-4 rounded-xl bg-red-50 dark:bg-red-900/20"><p class="text-2xl font-bold text-red-600 dark:text-red-400">0</p><p class="text-xs text-gray-500 dark:text-gray-400">Alpha</p></div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Gaji</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between"><dt class="text-sm text-gray-500 dark:text-gray-400">Gaji Pokok</dt><dd class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($employee->base_salary ?? 0, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-sm text-gray-500 dark:text-gray-400">Tunjangan Absensi</dt><dd class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($employee->allowance_absensi ?? 0, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-sm text-gray-500 dark:text-gray-400">Tunjangan Transport</dt><dd class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($employee->allowance_transport ?? 0, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-sm text-gray-500 dark:text-gray-400">Tunjangan Jabatan</dt><dd class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($employee->allowance_jabatan ?? 0, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-sm text-gray-500 dark:text-gray-400">Tunjangan Insentif</dt><dd class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($employee->allowance_insentif ?? 0, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-sm text-gray-500 dark:text-gray-400">Uang Lembur / Jam</dt><dd class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($employee->overtime_pay_per_hour ?? 0, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-sm text-gray-500 dark:text-gray-400">Uang Makan Lembur</dt><dd class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($employee->uang_makan_lembur ?? 0, 0, ',', '.') }}</dd></div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between"><dt class="text-sm font-semibold text-gray-900 dark:text-white">Total</dt><dd class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format(($employee->base_salary ?? 0) + ($employee->allowance_absensi ?? 0) + ($employee->allowance_transport ?? 0) + ($employee->allowance_jabatan ?? 0) + ($employee->allowance_insentif ?? 0), 0, ',', '.') }}</dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Bank</h3>
                <dl class="space-y-3">
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bank</dt><dd class="text-sm text-gray-900 dark:text-white">{{ $employee->bank_name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">No. Rekening</dt><dd class="text-sm text-gray-900 dark:text-white">{{ $employee->bank_account ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Atas Nama</dt><dd class="text-sm text-gray-900 dark:text-white">{{ $employee->bank_holder ?? '-' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Buat Absensi Manual
                    </a>
                    <a href="{{ route('admin.leaves.create', ['employee_id' => $employee->id]) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Ajukan Cuti
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Lihat Slip Gaji
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection
        