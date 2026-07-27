@extends('layouts.admin')

@section('page-title', 'Activity Log')
@section('page-subtitle', 'Riwayat aktivitas sistem')

@section('page-content')
<div class="space-y-6">
    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">User</label>
                <select name="user_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Aksi</label>
                <select name="action" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tipe</label>
                <select name="log_type" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($logTypes as $t)
                    <option value="{{ $t }}" {{ request('log_type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Deskripsi..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Filter</button>
            <button type="button" onclick="window.location.href='{{ route('admin.activity-logs.index') }}'" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Reset</button>
        </div>
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Waktu</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">User</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tipe</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Deskripsi</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400 text-xs whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs font-semibold text-gray-600 dark:text-gray-400">
                                    {{ strtoupper(substr($log->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="text-gray-900 dark:text-white">{{ $log->user?->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ 
                                $log->action === 'Create' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 
                                ($log->action === 'Update' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 
                                ($log->action === 'Delete' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 
                                'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400')) }}">{{ $log->action }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-gray-400">{{ $log->log_type ?? '-' }}</td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                        <td class="py-3 px-4 text-gray-500 dark:text-gray-500 text-xs font-mono">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
