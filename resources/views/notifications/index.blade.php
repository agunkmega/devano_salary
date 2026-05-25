@extends('layouts.admin')

@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Daftar notifikasi')

@section('page-content')
<div x-data="notificationList()" x-init="init()" class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total <span x-text="notifications.length"></span> notifikasi</p>
        <button @click="markAllRead" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Tandai semua sudah dibaca</button>
    </div>

    <div class="space-y-2">
        <template x-for="notif in notifications" :key="notif.id">
            <div class="flex items-start gap-4 p-4 rounded-2xl border transition-all duration-200 cursor-pointer hover:shadow-sm" :class="notif.read ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' : 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800'">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" :class="notif.read ? 'bg-gray-100 dark:bg-gray-700' : 'bg-blue-100 dark:bg-blue-900/30'">
                    <svg class="w-5 h-5" :class="notif.read ? 'text-gray-400' : 'text-blue-600 dark:text-blue-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium" :class="notif.read ? 'text-gray-900 dark:text-white' : 'text-gray-900 dark:text-white'" x-text="notif.title"></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" x-text="notif.message"></p>
                    <p class="text-xs text-gray-400 mt-1" x-text="notif.time"></p>
                </div>
                <div class="flex-shrink-0 flex items-center gap-1">
                    <template x-if="!notif.read">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    </template>
                    <button @click.stop="toggleRead(notif)" class="p-1 text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationList', () => ({
            notifications: [
                { id: 1, title: 'Cuti Baru Diajukan', message: 'Budi Santoso mengajukan cuti tahunan 3 hari', time: '5 menit lalu', read: false },
                { id: 2, title: 'Pengajuan Kasbon', message: 'Siti Aisyah mengajukan kasbon Rp 2.000.000', time: '1 jam lalu', read: false },
                { id: 3, title: 'Cuti Disetujui', message: 'Pengajuan cuti Ahmad Rizal telah disetujui', time: '3 jam lalu', read: true },
                { id: 4, title: 'Payroll Tersedia', message: 'Payroll Januari 2024 sudah tersedia', time: '1 hari lalu', read: true },
                { id: 5, title: 'Karyawan Baru', message: 'Data karyawan Dewi Lestari telah ditambahkan', time: '2 hari lalu', read: true },
            ],
            toggleRead(notif) { notif.read = !notif.read; },
            markAllRead() { this.notifications.forEach(n => n.read = true); },
            init() {}
        }));
    });
</script>
@endpush
@endsection
