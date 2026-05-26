<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Portal Karyawan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-950 font-sans antialiased">
    @if(session()->has('portal_employee_id'))
    <nav class="sticky top-0 z-50 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm">
        <div class="max-w-lg mx-auto px-4 h-14 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ config('app.name') }}</span>
            <form action="{{ route('portal.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">Keluar</button>
            </form>
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
</body>
</html>
