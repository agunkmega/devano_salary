@extends('portal.layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('portal.dashboard') }}" class="w-9 h-9 rounded-xl bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 active:scale-95 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Ajukan Kasbon</h2>
            <p class="text-xs text-gray-400">Isi form berikut untuk mengajukan kasbon</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5">
        <form method="POST" action="{{ route('portal.cash-advance.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah Pinjaman</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">Rp</span>
                    <input type="number" name="amount" required min="1000" class="w-full pl-11 pr-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all" placeholder="0">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-900/20 transition-all">
                        <input type="radio" name="type" value="tunai" checked class="sr-only peer">
                        <svg class="w-4 h-4 text-gray-500 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300 peer-checked:text-blue-600 peer-checked:font-medium">Tunai</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-900/20 transition-all">
                        <input type="radio" name="type" value="non_tunai" class="sr-only peer">
                        <svg class="w-4 h-4 text-gray-500 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300 peer-checked:text-blue-600 peer-checked:font-medium">Non Tunai</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Cicilan (bulan)</label>
                <select name="installments" required class="w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all appearance-none">
                    @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ $i }}x cicilan</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Keterangan</label>
                <textarea name="description" rows="3" class="w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-all resize-none" placeholder="Alasan pengajuan kasbon..."></textarea>
            </div>
            <button type="submit" class="w-full py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] transition-all shadow-sm">Kirim Pengajuan</button>
        </form>
    </div>
</div>
@endsection