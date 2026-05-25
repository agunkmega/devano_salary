@extends('layouts.admin')

@section('page-title', 'Activity Log')
@section('page-subtitle', 'Riwayat aktivitas sistem')

@section('page-content')
<div x-data="activityLog()" x-init="init()" class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                <input type="date" x-model="filters.start_date" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                <input type="date" x-model="filters.end_date" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">User</label>
                <input type="text" x-model="filters.user" placeholder="Cari user..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Aksi</label>
                <select x-model="filters.action" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option>Create</option>
                    <option>Update</option>
                    <option>Delete</option>
                    <option>Login</option>
                    <option>Logout</option>
                </select>
            </div>
            <button @click="filterData" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Filter</button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Waktu</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">User</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Deskripsi</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="log in filteredLogs" :key="log.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400 text-xs" x-text="log.time"></td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs font-semibold text-gray-600 dark:text-gray-400" x-text="log.initials"></div>
                                    <span class="text-gray-900 dark:text-white" x-text="log.user"></span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="{
                                    'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400': log.action === 'Create',
                                    'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400': log.action === 'Update',
                                    'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400': log.action === 'Delete',
                                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400': log.action === 'Login' || log.action === 'Logout'
                                }" x-text="log.action"></span>
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400 max-w-xs truncate" x-text="log.description"></td>
                            <td class="py-3 px-4 text-gray-500 dark:text-gray-500 text-xs font-mono" x-text="log.ip"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">Menampilkan <span x-text="filteredLogs.length"></span> data</p>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('activityLog', () => ({
            filters: { start_date: '', end_date: '', user: '', action: '' },
            logs: [
                { id: 1, time: '2024-01-15 08:30:15', user: 'Admin', initials: 'A', action: 'Create', description: 'Menambahkan pegawai baru: Budi Santoso', ip: '192.168.1.1' },
                { id: 2, time: '2024-01-15 09:15:22', user: 'Admin', initials: 'A', action: 'Update', description: 'Mengubah data pegawai: Siti Aisyah', ip: '192.168.1.1' },
                { id: 3, time: '2024-01-15 10:00:00', user: 'Admin', initials: 'A', action: 'Delete', description: 'Menghapus departemen: Marketing', ip: '192.168.1.1' },
                { id: 4, time: '2024-01-15 10:30:45', user: 'Budi Santoso', initials: 'BS', action: 'Login', description: 'User login ke sistem', ip: '192.168.1.100' },
                { id: 5, time: '2024-01-15 11:00:00', user: 'Admin', initials: 'A', action: 'Create', description: 'Generate payroll Januari 2024', ip: '192.168.1.1' },
            ],
            get filteredLogs() {
                return this.logs.filter(l => {
                    if (this.filters.action && l.action !== this.filters.action) return false;
                    if (this.filters.user && !l.user.toLowerCase().includes(this.filters.user.toLowerCase())) return false;
                    return true;
                });
            },
            filterData() {},
            init() {}
        }));
    });
</script>
@endpush
@endsection
