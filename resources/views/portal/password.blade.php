@extends('portal.layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('portal.dashboard') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
    <div class="text-center mb-6">
        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
            <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ubah Password</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Buat password baru untuk akun portal kamu</p>
    </div>

    <form method="POST" action="{{ route('portal.password.update') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Saat Ini</label>
                <input type="password" name="current_password" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all @error('current_password') border-red-300 dark:border-red-700 @enderror"
                    placeholder="Masukkan password saat ini">
                @error('current_password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                <input type="password" name="password" required minlength="6"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all @error('password') border-red-300 dark:border-red-700 @enderror"
                    placeholder="Minimal 6 karakter">
                @error('password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required minlength="6"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                    placeholder="Ulangi password baru">
            </div>

            <button type="submit"
                class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                Simpan Password
            </button>
        </div>
    </form>
</div>
@endsection
