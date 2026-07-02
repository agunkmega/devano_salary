@extends('layouts.admin')

@section('page-title', 'Jenis Cuti')
@section('page-subtitle', 'Kelola jenis cuti dan izin')

@section('page-content')
<div class="space-y-6">
    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex justify-end">
        <a href="{{ route('admin.leave-types.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jenis Cuti
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Kode</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Keterangan</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Dibayar</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Max Hari</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aktif</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveTypes as $lt)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-4 font-mono text-sm font-medium text-gray-900 dark:text-white">{{ $lt->code }}</td>
                        <td class="py-3 px-4 text-gray-900 dark:text-white font-medium">{{ $lt->name }}</td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $lt->description ?? '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($lt->is_paid)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">Ya</span>
                            @else
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Tidak</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center text-gray-900 dark:text-white">{{ $lt->max_days_per_year ?? '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($lt->is_active)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">Aktif</span>
                            @else
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.leave-types.edit', $lt->id) }}" class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.leave-types.destroy', $lt->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus jenis cuti ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada jenis cuti</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ $leaveTypes->firstItem() }} - {{ $leaveTypes->lastItem() }} dari {{ $leaveTypes->total() }}
            </p>
            {{ $leaveTypes->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection