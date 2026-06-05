<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body x-data="guestTheme()" x-init="init()" class="font-sans text-gray-900 dark:text-white antialiased">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 dark:from-gray-900 dark:via-indigo-950 dark:to-gray-950">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+')] opacity-30"></div>
                <div class="absolute top-[-20%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-400/20 blur-3xl"></div>
                <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] rounded-full bg-purple-400/20 blur-3xl"></div>
                <div class="absolute top-[40%] right-[-5%] w-[25%] h-[25%] rounded-full bg-indigo-300/15 blur-3xl"></div>
            </div>
            <button @click="darkMode = !darkMode" class="fixed top-4 right-4 p-2 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-colors bg-white/10 backdrop-blur-sm border border-white/10 shadow-sm z-50">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
            <div class="relative z-10 flex flex-col items-center">
                <div class="mb-8">
                    <a href="/">
                        @php $logoPath = \App\Models\Setting::where('key', 'app_logo')?->value('value'); @endphp
                        @if($logoPath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="Logo" class="h-28 w-auto drop-shadow-lg">
                        @else
                        <x-application-logo class="w-20 h-20 fill-current text-white/80" />
                        @endif
                    </a>
                </div>
                <div class="w-full sm:max-w-md mt-6 px-8 py-6 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl shadow-2xl dark:shadow-black/30 overflow-hidden sm:rounded-2xl border border-white/30 dark:border-gray-700/50">
                    {{ $slot }}
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('guestTheme', () => ({
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
