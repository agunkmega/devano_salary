@extends('layouts.admin')

@section('page-title', 'Laporan Keterlambatan')
@section('page-subtitle', 'Rekap keterlambatan karyawan')

@section('page-content')
<div x-data="reportLateness()" x-init="init()" class="space-y-6">
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

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Keterlambatan</p>
            <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-1">47</p>
            <p class="text-xs text-gray-400 mt-1">Kali</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Keterlambatan</p>
            <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-1">15</p>
            <p class="text-xs text-gray-400 mt-1">Menit</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Denda</p>
            <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-1">Rp 1.175.000</p>
            <p class="text-xs text-gray-400 mt-1">@ Rp 25.000 / menit</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Keterlambatan</h3>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Excel</button>
                <button onclick="window.print()" class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Print</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Departemen</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jam Masuk</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Telat (menit)</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Denda</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in lateData" :key="item.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="py-3 px-4 text-gray-900 dark:text-white" x-text="item.date"></td>
                            <td class="py-3 px-4"><span class="text-gray-900 dark:text-white font-medium" x-text="item.name"></span></td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400" x-text="item.department"></td>
                            <td class="py-3 px-4 font-mono text-gray-600 dark:text-gray-400" x-text="item.clock_in"></td>
                            <td class="py-3 px-4 text-center text-orange-600 dark:text-orange-400 font-medium" x-text="item.late_minutes"></td>
                            <td class="py-3 px-4 text-right text-red-600 dark:text-red-400" x-text="item.fine"></td>
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
        Alpine.data('reportLateness', () => ({
            filters: { start_date: '', end_date: '', department: '', employee: '' },
            lateData: [
                { id: 1, date: '2024-01-15', name: 'Siti Aisyah', department: 'HRD', clock_in: '08:30', late_minutes: 15, fine: 'Rp 25.000' },
                { id: 2, date: '2024-01-14', name: 'Budi Santoso', department: 'IT', clock_in: '08:20', late_minutes: 10, fine: 'Rp 10.000' },
                { id: 3, date: '2024-01-14', name: 'Dewi Lestari', department: 'Marketing', clock_in: '08:45', late_minutes: 30, fine: 'Rp 50.000' },
            ],
            generate() {},
            init() {}
        }));
    });
</script>
@endpush
@endsection
