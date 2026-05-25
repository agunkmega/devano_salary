@extends('layouts.admin')

@section('page-title', 'Laporan Absensi')
@section('page-subtitle', 'Rekap kehadiran karyawan')

@section('page-content')
<div x-data="reportAttendance()" x-init="init()" class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                <input type="date" x-model="filters.start_date" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                <input type="date" x-model="filters.end_date" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Departemen</label>
                <select x-model="filters.department" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option>IT</option>
                    <option>HRD</option>
                    <option>Finance</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pegawai</label>
                <input type="text" x-model="filters.employee" placeholder="Cari..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <button @click="generate" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Generate</button>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">96</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Hadir</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">12</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Terlambat</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">8</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Izin / Sakit</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">4</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Alpha</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Laporan</h3>
            <div class="flex gap-2">
                <button class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Excel
                </button>
                <button class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">PDF</button>
                <button onclick="window.print()" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Departemen</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Hadir</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Terlambat</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Izin</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Sakit</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Cuti</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Alpha</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">%</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="r in reportData" :key="r.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-3 text-gray-900 dark:text-white font-medium" x-text="r.name"></td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400" x-text="r.department"></td>
                            <td class="py-3 px-3 text-center text-gray-900 dark:text-white" x-text="r.hadir"></td>
                            <td class="py-3 px-3 text-center text-orange-600 dark:text-orange-400" x-text="r.terlambat"></td>
                            <td class="py-3 px-3 text-center text-blue-600 dark:text-blue-400" x-text="r.izin"></td>
                            <td class="py-3 px-3 text-center text-purple-600 dark:text-purple-400" x-text="r.sakit"></td>
                            <td class="py-3 px-3 text-center text-indigo-600 dark:text-indigo-400" x-text="r.cuti"></td>
                            <td class="py-3 px-3 text-center text-red-600 dark:text-red-400" x-text="r.alpha"></td>
                            <td class="py-3 px-3 text-center font-semibold" :class="r.percentage >= 90 ? 'text-emerald-600' : r.percentage >= 75 ? 'text-orange-600' : 'text-red-600'" x-text="r.percentage + '%'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportAttendance', () => ({
            filters: { start_date: '', end_date: '', department: '', employee: '' },
            reportData: [
                { id: 1, name: 'Budi Santoso', department: 'IT', hadir: 20, terlambat: 2, izin: 1, sakit: 0, cuti: 0, alpha: 0, percentage: 95 },
                { id: 2, name: 'Siti Aisyah', department: 'HRD', hadir: 18, terlambat: 3, izin: 0, sakit: 1, cuti: 0, alpha: 1, percentage: 86 },
                { id: 3, name: 'Ahmad Rizal', department: 'Finance', hadir: 22, terlambat: 0, izin: 0, sakit: 0, cuti: 1, alpha: 0, percentage: 100 },
            ],
            generate() {},
            init() {}
        }));
    });
</script>
@endpush
@endsection
