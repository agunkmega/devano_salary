@extends('layouts.admin')

@section('page-title', 'Riwayat Import Absensi')
@section('page-subtitle', 'Log import data dari mesin absensi')

@section('page-content')
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
        <table class="w-full text-sm">
            <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                    <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">File</th>
                    <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Tipe</th>
                    <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Total</th>
                    <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Sukses</th>
                    <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Gagal</th>
                    <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                    <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">User</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="py-3 px-3 text-gray-900 dark:text-white">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3 px-3 text-gray-600 dark:text-gray-400">{{ $log->file_name }}</td>
                    <td class="py-3 px-3">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase">{{ $log->file_type }}</span>
                    </td>
                    <td class="py-3 px-3 text-center text-gray-900 dark:text-white">{{ $log->total_records }}</td>
                    <td class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400">{{ $log->success_records }}</td>
                    <td class="py-3 px-3 text-center {{ $log->failed_records > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $log->failed_records }}</td>
                    <td class="py-3 px-3">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                            @if($log->status == 'completed' || $log->status == 'success') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400
                            @elseif($log->status == 'partial') bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400
                            @else bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400
                            @endif">
                            {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-gray-600 dark:text-gray-400">{{ $log->user?->name ?? 'System' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat import</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection