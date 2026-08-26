<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Portal Karyawan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            var stored = localStorage.getItem('darkMode');
            if (stored === 'true' || (stored === null && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <style>[x-cloak] { display: none !important; }
        .dark input[type="date"], .dark select { color-scheme: dark; }
        .dark input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); }
    </style>
</head>
<body x-data="portalApp()" x-init="init()" class="bg-gray-50 dark:bg-gray-950 font-sans antialiased pb-20">

    <main class="max-w-lg mx-auto px-4 pt-4 pb-4">
        @if(session('success'))
        <div class="mb-3 p-3 text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl border border-emerald-200 dark:border-emerald-800">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-3 p-3 text-sm text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/50 rounded-xl border border-red-200 dark:border-red-800">
            @foreach($errors->all() as $err)
            <p>{{ $err }}</p>
            @endforeach
        </div>
        @endif

        @yield('content')
    </main>

    @if(session()->has('portal_employee_id'))
    @php
        $deptHeadDept = \App\Models\Department::where('department_head_id', session('portal_employee_id'))->first();
        $isDeptHead = $deptHeadDept ? true : false;
        $pendingCount = $isDeptHead ? \App\Models\Leave::where('status', 'pending')->whereHas('employee', fn($q) => $q->where('department_id', $deptHeadDept->id))->where('employee_id', '!=', session('portal_employee_id'))->count() : 0;
    @endphp
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] dark:shadow-[0_-4px_20px_rgba(0,0,0,0.3)]">
        <div class="max-w-lg mx-auto px-2 h-16 flex items-center justify-around">
            <a href="{{ route('portal.dashboard') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl {{ request()->routeIs('portal.dashboard') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] font-medium">Beranda</span>
            </a>
            <a href="{{ route('portal.attendance.history') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl {{ request()->routeIs('portal.attendance.*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="text-[10px] font-medium">Absensi</span>
            </a>
            <a href="{{ route('portal.leave.create') }}" @click.prevent="showTrialNotice = true" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl {{ request()->routeIs('portal.leave.*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-[10px] font-medium">Cuti</span>
            </a>
            @if($isDeptHead)
            <a href="{{ route('portal.leave-approval.index') }}" class="relative flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl {{ request()->routeIs('portal.leave-approval.*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[10px] font-medium">Setujui</span>
                @if($pendingCount > 0)
                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center">{{ $pendingCount > 9 ? '9+' : $pendingCount }}</span>
                @endif
            </a>
            @endif
            <a href="{{ route('portal.cash-advance.create') }}" @click.prevent="showTrialNotice = true" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl {{ request()->routeIs('portal.cash-advance.*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[10px] font-medium">Kasbon</span>
            </a>
            <form action="{{ route('portal.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-gray-400 dark:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="text-[10px] font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </nav>
    @endif

    {{-- Popup: menu dalam uji coba --}}
    <div x-show="showTrialNotice" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showTrialNotice = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-5 max-w-xs w-full text-center" @click.stop>
            <div class="w-12 h-12 mx-auto mb-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">Menu ini masih dalam uji coba</p>
            <button type="button" @click="showTrialNotice = false" class="mt-4 w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">OK</button>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('portalApp', () => ({
                darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                showTrialNotice: false,
                init() {
                    this.$watch('darkMode', val => {
                        localStorage.setItem('darkMode', val);
                        document.documentElement.classList.toggle('dark', val);
                    });
                    document.documentElement.classList.toggle('dark', this.darkMode);
                },
                toggleDark() {
                    this.darkMode = !this.darkMode;
                }
            }));
        });
    </script>
</body>
</html>