@extends('layouts.admin')

@section('page-title', 'Detail User')
@section('page-subtitle', $user->name)

@section('page-content')
<div class="max-w-3xl mx-auto space-y-6">
    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-blue-600 flex items-center justify-center text-white text-xl font-bold">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Role</p>
                <p class="mt-1">
                    <span class="text-sm font-medium px-2.5 py-1 rounded-full {{ 
                        $user->role === 'super_admin' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 
                        ($user->role === 'hr' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 
                        ($user->role === 'manager' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 
                        'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400')) }}">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</p>
                <p class="mt-1">
                    <span class="text-sm font-medium px-2.5 py-1 rounded-full {{ $user->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Email</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">No. Telepon</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->phone ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Email Terverifikasi</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->email_verified_at ? $user->email_verified_at->format('d M Y H:i') : 'Belum' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Bergabung</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Kembali</a>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2.5 text-sm font-medium text-white bg-amber-600 rounded-xl hover:bg-amber-700 transition-colors">Edit</a>
        </div>
    </div>
</div>
@endsection
