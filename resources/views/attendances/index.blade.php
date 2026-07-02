@extends('layouts.admin')

@section('page-title', 'Absensi')
@section('page-subtitle', 'Kelola data kehadiran')

@section('page-content')
<div x-data="attendanceList()" x-init="init()" class="space-y-6">
    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
    @endif
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <form method="GET" action="{{ route('admin.attendances.index') }}" class="flex flex-wrap gap-3 items-end flex-1">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Departemen</label>
                    <select name="department_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pegawai</label>
                    <input type="text" name="employee" value="{{ request('employee') }}" placeholder="Nama pegawai..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Cari</button>
            </form>
            <div class="flex flex-wrap items-center gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select x-model="filters.status" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua</option>
                            <option>Hadir</option>
                            <option>Terlambat</option>
                            <option>Izin</option>
                            <option>Sakit</option>
                            <option>Cuti</option>
                            <option>Libur</option>
                            <option>Alpha</option>
                        </select>
                </div>
                <form x-ref="importForm" action="{{ route('admin.attendances.import-checkpoint') }}" method="POST" enctype="multipart/form-data" class="inline" @submit="importing = true">
                    @csrf
                    <label class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer" :class="{ 'opacity-50 pointer-events-none': importing }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="importing ? 'Importing...' : 'Import Checkpoint'"></span>
                        <input type="file" name="file" accept=".txt,.csv" class="hidden" :disabled="importing" @change="$refs.importForm.submit()">
                    </label>
                    @error('file')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </form>
                <button @click="showManualModal = true" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Manual
                </button>
                <a :href="`{{ route('admin.reports.attendance-print') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}&department_id={{ request('department_id') }}&employee=${encodeURIComponent(filters.employee)}&status=${encodeURIComponent(filters.status)}`" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </a>
            </div>
        </div>

        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Clock In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Break Out</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Break In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Clock Out</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Lembur In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Lembur Out</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Total Lembur</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Telat</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Pulang Awal</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Lebih Istirahat</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                        <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="attendances.length === 0">
                        <tr>
                            <td colspan="14" class="text-center py-12 text-gray-400 dark:text-gray-500">
                                @if($dateFrom && $dateTo && request('employee'))
                                <p>Tidak ada data absensi untuk filter tersebut.</p>
                                @else
                                <p>Isi rentang tanggal dan nama pegawai, lalu klik <strong>Cari</strong> untuk menampilkan data.</p>
                                @endif
                            </td>
                        </tr>
                    </template>
                    <template x-for="att in filteredAttendances" :key="att.employee_id + '|' + att.date">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-3">
                                <div class="font-medium" :class="att.day_name === 'Minggu' ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'" x-text="att.date"></div>
                                <div class="text-xs" :class="att.day_name === 'Minggu' ? 'text-red-400 dark:text-red-500' : 'text-gray-400 dark:text-gray-500'" x-text="att.day_name"></div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs font-semibold text-gray-600 dark:text-gray-300" x-text="att.initials"></div>
                                    <span class="text-gray-900 dark:text-white" x-text="att.employee"></span>
                                </div>
                            </td>
                            <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400" x-text="att.clock_in || '-'"></td>
                            <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400" x-text="att.break_out || '-'"></td>
                            <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400" x-text="att.break_in || '-'"></td>
                            <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400" x-text="att.clock_out || '-'"></td>
                            <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400" x-text="att.overtime_in || '-'"></td>
                            <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400" x-text="att.overtime_out || '-'"></td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400" x-text="att.overtime_minutes != null ? Math.floor(att.overtime_minutes / 60) + 'j ' + (att.overtime_minutes % 60) + 'm' : '-'"></td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400">
                                <template x-if="att.ignore_late">
                                    <span class="text-xs font-semibold text-orange-500 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 px-1.5 py-0.5 rounded">Diabaikan</span>
                                </template>
                                <template x-if="!att.ignore_late">
                                    <span x-text="att.late_minutes > 0 ? att.late_minutes + ' menit' : '-'"></span>
                                </template>
                            </td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400">
                                <template x-if="att.ignore_early_leave">
                                    <span class="text-xs font-semibold text-purple-500 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20 px-1.5 py-0.5 rounded">Diabaikan</span>
                                </template>
                                <template x-if="!att.ignore_early_leave">
                                    <span x-text="att.early_leave_minutes != null && att.early_leave_minutes > 0 ? att.early_leave_minutes + ' menit' : '-'"></span>
                                </template>
                            </td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400">
                                <template x-if="att.ignore_excess_break">
                                    <span class="text-xs font-semibold text-cyan-500 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-900/20 px-1.5 py-0.5 rounded">Diabaikan</span>
                                </template>
                                <template x-if="!att.ignore_excess_break">
                                    <span x-text="att.excess_break_minutes != null && att.excess_break_minutes > 0 ? att.excess_break_minutes + ' menit' : '-'"></span>
                                </template>
                            </td>
                            <td class="py-3 px-3">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="{
                                    'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400': att.status === 'Hadir',
                                    'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400': att.status === 'Terlambat',
                                    'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400': att.status === 'Izin' || att.status === 'Sakit',
                                    'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400': att.status === 'Cuti' || att.status === 'Libur' || (att.status === '-' && att.day_name === 'Minggu'),
                                    'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400': att.status === 'Alpha' || (att.status === '-' && att.day_name !== 'Minggu')
                                }" x-text="att.status === 'Cuti' && att.leave_type_name ? att.leave_type_name : (att.status === '-' ? (att.day_name === 'Minggu' ? 'Libur' : 'Alpha') : att.status)"></span>
                                <div x-show="att.status === 'Libur' && att.holiday_name" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="att.holiday_name"></div>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <button @click="openEditModal(att)" class="p-2 rounded-lg transition-colors text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot x-show="filteredAttendances.length > 0" class="bg-gray-50 dark:bg-gray-900/50 font-medium">
                    <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                        <td colspan="8" class="py-3 px-3 text-right text-gray-700 dark:text-gray-300 font-semibold">Total</td>
                        <td class="py-3 px-3 text-gray-700 dark:text-gray-300" x-text="
                            (() => {
                                let total = filteredAttendances.reduce((sum, a) => sum + (a.overtime_minutes || 0), 0);
                                return total > 0 ? Math.floor(total / 60) + 'j ' + (total % 60) + 'm' : '-';
                            })()
                        "></td>
                        <td class="py-3 px-3 text-gray-700 dark:text-gray-300" x-text="
                            (() => {
                                let total = filteredAttendances.reduce((sum, a) => sum + (a.late_minutes || 0), 0);
                                return total > 0 ? total + ' menit' : '-';
                            })()
                        "></td>
                        <td class="py-3 px-3 text-gray-700 dark:text-gray-300" x-text="
                            (() => {
                                let total = filteredAttendances.reduce((sum, a) => sum + (a.early_leave_minutes || 0), 0);
                                return total > 0 ? total + ' menit' : '-';
                            })()
                        "></td>
                        <td class="py-3 px-3 text-gray-700 dark:text-gray-300" x-text="
                            (() => {
                                let total = filteredAttendances.reduce((sum, a) => sum + ((a.excess_break_minutes || 0) > 0 ? a.excess_break_minutes : 0), 0);
                                return total > 0 ? total + ' menit' : '-';
                            })()
                        "></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div x-show="filteredAttendances.length > 0" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Menampilkan <span x-text="filteredAttendances.length"></span> data</p>
            <p class="text-xs text-gray-400" x-data="{ dates: [...new Set(attendances.map(a => a.date))].sort() }" x-text="'Rentang: ' + dates[0] + ' s/d ' + dates[dates.length-1] + ' (' + dates.length + ' hari)'"></p>
        </div>
    </div>

    <div x-show="showManualModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showManualModal = false">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showManualModal = false"></div>
        <div x-show="showManualModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tambah Absensi Manual</h3>
            <form action="{{ route('admin.attendances.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pegawai</label>
                        <select name="employee_id" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Pegawai</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal</label>
                        <input type="date" name="date" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Clock In</label>
                            <input type="time" name="clock_in" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Clock Out</label>
                            <input type="time" name="clock_out" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                        <select name="status" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="cuti">Cuti</option>
                            <option value="alpha">Alpha</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showManualModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="importing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 max-w-sm w-full mx-4 text-center">
            <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-900 dark:text-white font-medium">Mengimport data...</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Harap tunggu, jangan tutup halaman</p>
            <div class="mt-4 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-blue-600 rounded-full animate-pulse" style="width: 60%"></div>
            </div>
        </div>
    </div>
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="editModal = false">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="editModal = false"></div>
        <div x-show="editModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Absensi</h3>
            <form :action="editing?.id ? `/admin/attendances/${editing.id}` : '{{ route('admin.attendances.store') }}'" method="POST">
                @csrf
                <template x-if="editing?.id">
                    @method('PUT')
                </template>
                <input type="hidden" name="employee_id" :value="editing?.employee_id">
                <input type="hidden" name="attendance_date" :value="editing?.date">
                <input type="hidden" name="manual_reason" value="Edit from web">
                <input type="hidden" name="date_from" :value="currentParams.date_from">
                <input type="hidden" name="date_to" :value="currentParams.date_to">
                <input type="hidden" name="department_id" :value="currentParams.department_id">
                <input type="hidden" name="employee" :value="filters.employee">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Clock In</label>
                            <input type="time" x-model="editForm.clock_in" name="clock_in" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Break Out</label>
                            <input type="time" x-model="editForm.break_out" name="break_out" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Break In</label>
                            <input type="time" x-model="editForm.break_in" name="break_in" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Clock Out</label>
                            <input type="time" x-model="editForm.clock_out" name="clock_out" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Lembur In</label>
                            <input type="time" x-model="editForm.overtime_in" name="overtime_in" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Lembur Out</label>
                            <input type="time" x-model="editForm.overtime_out" name="overtime_out" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" x-model="editForm.ignore_late" name="ignore_late" id="edit_ignore_late" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <label for="edit_ignore_late" class="text-sm text-gray-700 dark:text-gray-300">Abaikan telat</label>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" x-model="editForm.ignore_early_leave" name="ignore_early_leave" id="edit_ignore_early_leave" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <label for="edit_ignore_early_leave" class="text-sm text-gray-700 dark:text-gray-300">Abaikan pulang awal</label>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" x-model="editForm.ignore_excess_break" name="ignore_excess_break" id="edit_ignore_excess_break" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <label for="edit_ignore_excess_break" class="text-sm text-gray-700 dark:text-gray-300">Abaikan lebih istirahat</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                        <select x-model="editForm.status" name="status" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <optgroup label="Cuti">
                                <option value="cuti">Cuti</option>
                                @foreach($leaveTypes ?? [] as $lt)
                                <option value="cuti">{{ $lt->name }}</option>
                                @endforeach
                            </optgroup>
                            <option value="alpha">Alpha</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('attendanceList', () => ({
            filters: { employee: new URLSearchParams(window.location.search).get('employee') || '', status: '' },
            get currentParams() { return Object.fromEntries(new URLSearchParams(window.location.search)); },
            importing: false,
            showManualModal: false,
            editModal: false,
            editing: null,
            editForm: { clock_in: '', break_out: '', break_in: '', clock_out: '', overtime_in: '', overtime_out: '', status: '', ignore_late: false },
            departments: @json($departments),
            attendances: @json($attendancesData),
            get filteredAttendances() { return this.attendances.filter(a => { let status = a.status === '-' ? (a.day_name === 'Minggu' ? 'Libur' : 'Alpha') : a.status; if (this.filters.status && status !== this.filters.status) return false; if (this.filters.employee && !a.employee.toLowerCase().includes(this.filters.employee.toLowerCase())) return false; return true; }); },
            openEditModal(att) { this.editing = att; this.editForm = { clock_in: att.clock_in || '', break_out: att.break_out || '', break_in: att.break_in || '', clock_out: att.clock_out || '', overtime_in: att.overtime_in || '', overtime_out: att.overtime_out || '', status: att.status && att.status !== '-' ? att.status.toLowerCase() : (att.day_name === 'Minggu' ? 'hadir' : 'alpha'), ignore_late: att.ignore_late || false, ignore_early_leave: att.ignore_early_leave || false, ignore_excess_break: att.ignore_excess_break || false }; this.editModal = true; },
            init() {}
        }));
    });
</script>
@endpush
@endsection
