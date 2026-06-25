@extends('layouts.admin')

@section('page-title', 'Riwayat Webhook Finger Spot')
@section('page-subtitle', 'Data absensi yang masuk otomatis dari mesin')

@section('page-content')
<div class="space-y-6">
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total Data</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $today }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Hari Ini</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">✓</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Webhook Aktif</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nama</label>
                <input type="text" name="name" value="{{ request('name') }}" placeholder="Cari nama..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500 w-48">
            </div>
            <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Filter</button>
            <a href="{{ route('admin.fingerspot.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Reset</a>
            <a href="{{ route('admin.fingerspot.export', request()->query()) }}" class="px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
        </form>

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Masuk via Webhook</h3>
            <a href="{{ route('admin.settings.index', ['tab' => 'fingerspot']) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Pengaturan</a>
        </div>

        @if($recent->count() > 0)
        <div class="overflow-x-auto max-h-[60vh] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Waktu Masuk</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">NIK</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Clock In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Break Out</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Break In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Clock Out</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Lembur In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Lembur Out</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent as $a)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400 text-xs">{{ $a->created_at->format('d M H:i') }}</td>
                        <td class="py-3 px-3 text-gray-900 dark:text-white font-mono text-xs">{{ $a->employee->nik ?? '-' }}</td>
                        <td class="py-3 px-3 text-gray-900 dark:text-white">{{ $a->employee->full_name ?? '-' }}</td>
                        <td class="py-3 px-3 text-gray-900 dark:text-white">{{ $a->attendance_date instanceof Carbon\Carbon ? $a->attendance_date->format('d M Y') : $a->attendance_date }}</td>
                        <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400 text-xs">{{ $a->clock_in ? Carbon\Carbon::parse($a->clock_in)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400 text-xs">{{ $a->break_out ? Carbon\Carbon::parse($a->break_out)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400 text-xs">{{ $a->break_in ? Carbon\Carbon::parse($a->break_in)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400 text-xs">{{ $a->clock_out ? Carbon\Carbon::parse($a->clock_out)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-orange-600 dark:text-orange-400 text-xs">{{ $a->overtime_in ? Carbon\Carbon::parse($a->overtime_in)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-orange-600 dark:text-orange-400 text-xs">{{ $a->overtime_out ? Carbon\Carbon::parse($a->overtime_out)->format('H:i') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada data untuk periode ini.</p>
        @endif
    </div>
</div>
@endsection
