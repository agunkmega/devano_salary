@extends('layouts.admin')

@section('page-title', 'Laporan Cuti & Izin')
@section('page-subtitle', 'Rekap cuti dan izin karyawan')

@section('page-content')
<div x-data="reportLeave()" x-init="init()" class="space-y-6">
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
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select x-model="filters.status" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
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

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">3</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Disetujui</p>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">12</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Ditolak</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">2</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Hari</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">45</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Cuti & Izin</h3>
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
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jenis</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Hari</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in leaveData" :key="item.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="py-3 px-4 text-gray-900 dark:text-white" x-text="item.date"></td>
                            <td class="py-3 px-4"><span class="text-gray-900 dark:text-white font-medium" x-text="item.name"></span></td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400" x-text="item.department"></td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="item.type === 'Cuti' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'" x-text="item.type"></span>
                            </td>
                            <td class="py-3 px-4 text-center text-gray-900 dark:text-white" x-text="item.days"></td>
                            <td class="py-3 px-4 text-center">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="{'bg-yellow-100 text-yellow-700': item.status === 'Pending', 'bg-green-100 text-green-700': item.status === 'Approved', 'bg-red-100 text-red-700': item.status === 'Rejected'}" x-text="item.status"></span>
                            </td>
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
        Alpine.data('reportLeave', () => ({
            filters: { start_date: '', end_date: '', status: '', department: '' },
            leaveData: [
                { id: 1, date: '2024-01-15', name: 'Siti Aisyah', department: 'HRD', type: 'Cuti', days: 2, status: 'Approved' },
                { id: 2, date: '2024-01-14', name: 'Budi Santoso', department: 'IT', type: 'Izin', days: 1, status: 'Pending' },
                { id: 3, date: '2024-01-13', name: 'Dewi Lestari', department: 'Marketing', type: 'Cuti', days: 3, status: 'Approved' },
                { id: 4, date: '2024-01-12', name: 'Ahmad Rizki', department: 'Finance', type: 'Izin', days: 1, status: 'Rejected' },
            ],
            generate() {},
            init() {}
        }));
    });
</script>
@endpush
@endsection