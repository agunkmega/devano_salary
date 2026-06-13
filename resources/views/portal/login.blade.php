@extends('portal.layouts.app')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center relative">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-950 dark:to-indigo-950 pointer-events-none">
        <div class="absolute top-[-10%] left-[-5%] w-[30%] h-[30%] rounded-full bg-blue-200/40 dark:bg-blue-800/20 blur-3xl"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[35%] h-[35%] rounded-full bg-indigo-200/40 dark:bg-indigo-800/20 blur-3xl"></div>
    </div>

    <div class="relative z-10 w-full max-w-sm">
        <div class="text-center mb-8">
            @php $logoPath = \App\Models\Setting::where('key', 'app_logo')?->value('value'); @endphp
            @if($logoPath)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="Logo" class="h-20 w-auto mx-auto mb-5 drop-shadow-lg">
            @else
            <div class="w-20 h-20 mx-auto mb-5 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 dark:shadow-blue-900/30">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            @endif
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Portal Karyawan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">Masuk dengan NIK atau Nomor Handphone</p>
        </div>

        <div class="bg-white/70 dark:bg-gray-900/70 backdrop-blur-xl rounded-2xl shadow-xl dark:shadow-black/30 border border-white/50 dark:border-gray-700/50 p-8">
            <form method="POST" action="{{ route('portal.login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">NIK / No. Handphone</label>
                    <input type="text" name="identity" value="{{ old('identity') }}" required autofocus class="w-full px-4 py-3.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-gray-400" placeholder="cth: 1011 atau 0812xxxx">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password <span class="text-xs text-gray-400 font-normal">(Tanggal Lahir: YYYY-MM-DD)</span></label>
                    <input type="password" name="password" required class="w-full px-4 py-3.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-gray-400" placeholder="Masukkan tanggal lahir">
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 text-[15px] font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] rounded-xl transition-all shadow-md">Masuk</button>
                </div>
            </form>
        </div>

        <p class="text-center mt-6 text-xs text-gray-400 dark:text-gray-500">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</div>
@endsection