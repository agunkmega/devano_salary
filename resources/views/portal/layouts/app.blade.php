<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Portal Karyawan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body x-data="portalTheme()" x-init="init()" class="bg-gray-50 dark:bg-gray-950 font-sans antialiased">
    <button @click="darkMode = !darkMode" class="fixed top-4 right-4 p-2 rounded-lg text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border border-gray-200 dark:border-gray-700 shadow-sm z-50">
        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    </button>
    @if(session()->has('portal_employee_id'))
    <nav class="sticky top-0 z-50 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm">
        <div class="max-w-lg mx-auto px-4 h-14 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ config('app.name') }}</span>
            <div class="flex items-center gap-2">
                <form action="{{ route('portal.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">Keluar</button>
                </form>
            </div>
        </div>
    </nav>
    @endif

    <main class="max-w-lg mx-auto px-4 py-6">
        @if(session('success'))
        <div class="mb-4 p-3 text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl border border-emerald-200 dark:border-emerald-800">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 p-3 text-sm text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/50 rounded-xl border border-red-200 dark:border-red-800">
            @foreach($errors->all() as $err)
            <p>{{ $err }}</p>
            @endforeach
        </div>
        @endif

        @yield('content')
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('portalTheme', () => ({
                darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                init() {
                    this.$watch('darkMode', val => {
                        localStorage.setItem('darkMode', val);
                        document.documentElement.classList.toggle('dark', val);
                    });
                    document.documentElement.classList.toggle('dark', this.darkMode);
                }
            }));
        });
    </script>
</body>
</html>
