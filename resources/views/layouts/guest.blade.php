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
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-950">
            <button @click="darkMode = !darkMode" class="fixed top-4 right-4 p-2 rounded-lg text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border border-gray-200 dark:border-gray-700 shadow-sm z-50">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500 dark:text-gray-400" />
                </a>
            </div>
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md dark:shadow-gray-900/50 overflow-hidden sm:rounded-lg border border-gray-200 dark:border-gray-700">
                {{ $slot }}
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
