@extends('layouts.admin')

@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Daftar notifikasi')

@section('page-content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total {{ $notifications->total() }} notifikasi ({{ $unreadCount }} belum dibaca)</p>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
            @csrf @method('PATCH')
            <button type="submit" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Tandai semua sudah dibaca</button>
        </form>
        @endif
    </div>

    <div class="space-y-2">
        @forelse($notifications as $notif)
        <div class="flex items-start gap-4 p-4 rounded-2xl border transition-all duration-200 {{ $notif->is_read ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' : 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800' }}">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $notif->is_read ? 'bg-gray-100 dark:bg-gray-700' : ($notif->icon === 'check' ? 'bg-emerald-100 dark:bg-emerald-900/30' : ($notif->icon === 'x' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-blue-100 dark:bg-blue-900/30')) }}">
                @if($notif->icon === 'check')
                <svg class="w-5 h-5 {{ $notif->is_read ? 'text-gray-400' : 'text-emerald-600 dark:text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @elseif($notif->icon === 'x')
                <svg class="w-5 h-5 {{ $notif->is_read ? 'text-gray-400' : 'text-red-600 dark:text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                @elseif($notif->icon === 'cash')
                <svg class="w-5 h-5 {{ $notif->is_read ? 'text-gray-400' : 'text-blue-600 dark:text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                @else
                <svg class="w-5 h-5 {{ $notif->is_read ? 'text-gray-400' : 'text-blue-600 dark:text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                @if($notif->url)
                <a href="{{ $notif->url }}" class="hover:underline">
                @endif
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $notif->title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $notif->message }}</p>
                @if($notif->url)
                </a>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex-shrink-0 flex items-center gap-1">
                @if(!$notif->is_read)
                <form method="POST" action="{{ route('admin.notifications.mark-read', $notif->id) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="p-1 text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-sm text-gray-500 dark:text-gray-400">Tidak ada notifikasi</div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="pt-4">
        {{ $notifications->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
