@extends('layouts.admin')

@section('page-title', 'Pengumuman')
@section('page-subtitle', 'Kelola pengumuman perusahaan')

@section('page-content')
<div class="space-y-6">
    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </form>
        <a href="{{ route('admin.announcements.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengumuman
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Judul</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Isi</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Dibuat</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aktif</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $a)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-4 text-gray-900 dark:text-white font-medium">{{ $a->title }}</td>
                        <td class="py-3 px-4 text-gray-500 dark:text-gray-400 max-w-md"><span class="line-clamp-2 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($a->content, 120) }}</span></td>
                        <td class="py-3 px-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $a->created_at->format('d/m/Y H:i') }}
                            @if($a->creator)
                            <span class="block text-xs text-gray-400">oleh {{ $a->creator->name }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($a->is_active)
                            <span class="inline-flex w-2 h-2 rounded-full bg-emerald-500"></span>
                            @else
                            <span class="inline-flex w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.announcements.edit', $a->id) }}" class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.announcements.destroy', $a->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pengumuman ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pengumuman</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection
