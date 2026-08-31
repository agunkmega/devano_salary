@extends('layouts.admin')

@section('page-title', 'Mesin Absensi')
@section('page-subtitle', 'Informasi dan status mesin fingerprint')

@section('page-content')
<div class="space-y-6">
    @if(session('sync_summary'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-2xl p-4">
            <p class="text-sm text-emerald-700 dark:text-emerald-300 font-medium">{{ session('sync_summary') }}</p>
            @if(session('sync_errors'))
                <ul class="mt-2 text-xs text-red-600 dark:text-red-400 list-disc list-inside">
                    @foreach(session('sync_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white font-mono text-sm">{{ $deviceId }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Device ID</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white font-mono text-xs">{{ $cloudId }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Cloud ID</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">●</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Status</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $employeeCount }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Karyawan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sinkron Data dari Fingerspot (get_attlog)</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Ambil absensi yang gagal/gagal terkirim ke webhook dari server Fingerspot. Rentang maksimal 2 hari per request, data tersedia hingga 60 hari ke belakang.</p>
            <form method="POST" action="{{ route('admin.fingerspot.sync') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ old('date_from', now()->subDays(2)->format('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ old('date_to', now()->format('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Sinkron Sekarang</button>
                    <span class="text-xs text-gray-400">Cloud ID: <span class="font-mono">{{ $cloudId }}</span></span>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Konfigurasi Mesin</h3>
            <table class="w-full text-sm">
                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="py-3 text-gray-500 dark:text-gray-400 w-48">API URL</td>
                    <td class="py-3 text-gray-900 dark:text-white font-mono text-xs">{{ $apiUrl }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="py-3 text-gray-500 dark:text-gray-400">Cloud Base URL</td>
                    <td class="py-3 text-gray-900 dark:text-white font-mono text-xs">{{ $cloudBaseUrl }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="py-3 text-gray-500 dark:text-gray-400">Device ID</td>
                    <td class="py-3 text-gray-900 dark:text-white font-mono text-xs">{{ $deviceId }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="py-3 text-gray-500 dark:text-gray-400">API Token</td>
                    <td class="py-3 text-gray-900 dark:text-white font-mono text-xs">
                        @if($apiToken)
                            <span class="text-emerald-600 dark:text-emerald-400">&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;</span>
                        @else
                            <span class="text-red-500">Belum diatur</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="py-3 text-gray-500 dark:text-gray-400">Webhook URL</td>
                    <td class="py-3 text-gray-900 dark:text-white font-mono text-xs break-all">
                        {{ url('api/fingerspot/webhook') }}
                    </td>
                </tr>
            </table>
            <div class="mt-4">
                <a href="{{ route('admin.settings.index', ['tab' => 'fingerspot']) }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors inline-block">Ubah Pengaturan</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Webhook Terakhir</h3>
            @if($lastWebhook)
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                        <td class="py-3 text-gray-500 dark:text-gray-400 w-36">Waktu</td>
                        <td class="py-3 text-gray-900 dark:text-white">{{ $lastWebhook->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                        <td class="py-3 text-gray-500 dark:text-gray-400">Karyawan</td>
                        <td class="py-3 text-gray-900 dark:text-white">{{ $lastWebhook->employee?->full_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-gray-500 dark:text-gray-400">Field</td>
                        <td class="py-3 text-gray-900 dark:text-white">
                            @php
                                $fields = ['clock_in', 'break_out', 'break_in', 'clock_out', 'overtime_in', 'overtime_out'];
                                $lastField = collect($fields)->first(fn($f) => $lastWebhook->$f);
                            @endphp
                            {{ $lastField ?? '-' }}
                        </td>
                    </tr>
                </table>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Belum ada data webhook masuk.</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Panduan Konfigurasi Mesin</h3>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-3">
                <p>1. Buka menu <strong>Settings / Network Settings</strong> pada mesin fingerprint.</p>
                <p>2. Masukkan URL server berikut pada field <strong>Server URL</strong> atau <strong>Cloud URL</strong>:</p>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-3 font-mono text-xs break-all select-all">
                    {{ url('api/fingerspot/webhook') }}
                </div>
                <p>3. Pastikan mesin terhubung ke internet (WiFi / LAN).</p>
                <p>4. Setelah terhubung, mesin akan otomatis mengirim data fingerprint ke server.</p>
                <p>5. Cek data masuk di halaman <a href="{{ route('admin.fingerspot.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Riwayat Webhook</a>.</p>
            </div>
        </div>
    </div>
</div>
@endsection
