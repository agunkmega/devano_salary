@extends('layouts.admin')

@section('page-title', 'Laporan Lembur')
@section('page-subtitle', 'Rekap lembur karyawan')

@section('page-content')
<div x-data="reportOvertime()" x-init="init()" class="space-y-6">
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
            <button @click="generate" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Generate</button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Lembur</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">128</p>
            <p class="text-xs text-gray-400 mt-1">Jam</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Lembur</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">2.5</p>
            <p class="text-xs text-gray-400 mt-1">Jam / hari</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Upah Lembur</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">Rp 4.800.000</p>
            <p class="text-xs text-gray-400 mt-1">1.5x upah / jam</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Lembur</h3>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Excel</button>
                <button onclick="window.print()" class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Print</button>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Departemen</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jam Lembur</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Upah Lembur</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in overtimeData" :key="item.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="py-3 px-4 text-gray-900 dark:text-white" x-text="item.date"></td>
                            <td class="py-3 px-4"><span class="text-gray-900 dark:text-white font-medium" x-text="item.name"></span></td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400" x-text="item.department"></td>
                            <td class="py-3 px-4 text-center text-blue-600 dark:text-blue-400 font-medium" x-text="item.hours"></td>
                            <td class="py-3 px-4 text-right text-green-600 dark:text-green-400" x-text="item.pay"></td>
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
        Alpine.data('reportOvertime', () => ({
            filters: { start_date: '', end_date: '', department: '' },
            overtimeData: [
                { id: 1, date: '2024-01-15', name: 'Siti Aisyah', department: 'HRD', hours: '2.5', pay: 'Rp 75.000' },
                { id: 2, date: '2024-01-14', name: 'Budi Santoso', department: 'IT', hours: '3.0', pay: 'Rp 90.000' },
                { id: 3, date: '2024-01-14', name: 'Dewi Lestari', department: 'Marketing', hours: '1.5', pay: 'Rp 45.000' },
            ],
            generate() {},
            init() {}
        }));
    });
</script>
@endpush
@endsection