@extends('portal.layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Riwayat Absensi</h2>
            <p class="text-xs text-gray-500">{{ $employee->full_name }}</p>
        </div>
        <a href="{{ route('portal.dashboard') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Kembali</a>
    </div>

    <form method="GET" action="{{ route('portal.attendance.history') }}" class="flex items-center gap-2">
        <select name="period" onchange="this.form.submit()" class="flex-1 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Periode</option>
            @foreach($periods as $p)
            <option value="{{ $p }}" {{ request('period') == $p ? 'selected' : '' }}>
                {{ \Carbon\Carbon::createFromFormat('Y-m', $p)->locale('id')->isoFormat('MMMM YYYY') }}
            </option>
            @endforeach
        </select>
    </form>

    @if($attendances->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @foreach($attendances as $att)
        @php
            $statusColors = [
                'hadir' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20',
                'terlambat' => 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20',
                'alpha' => 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20',
                'sakit' => 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20',
                'izin' => 'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20',
                'cuti' => 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20',
            ][$att->status] ?? 'text-gray-600 bg-gray-50';
        @endphp
        <div x-data="{ open: false }" class="transition-colors" :class="open ? 'bg-blue-50/50 dark:bg-blue-900/10' : ''">
            <div @click="open = !open" class="px-4 py-3 flex items-center justify-between cursor-pointer active:scale-[0.99] transition-transform select-none">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center">
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ $att->attendance_date->format('d') }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $att->attendance_date->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </p>
                        <div class="flex items-center gap-2 mt-0.5">
                            @if($att->clock_in)
                            <span class="text-[11px] text-gray-500">Masuk {{ $att->clock_in->format('H:i') }}</span>
                            @endif
                            @if($att->clock_out)
                            <span class="text-[11px] text-gray-400">·</span>
                            <span class="text-[11px] text-gray-500">Pulang {{ $att->clock_out->format('H:i') }}</span>
                            @endif
                            @if(!$att->clock_in && !$att->clock_out)
                            <span class="text-[11px] text-gray-400">Tidak ada absensi</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-semibold px-2 py-1 rounded-lg {{ $statusColors }}">
                        {{ ucfirst($att->status) }}
                    </span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            <div x-show="open" class="px-4 pb-3 border-t border-gray-100 dark:border-gray-700/50">
                <div class="grid grid-cols-2 gap-2 pt-3">
                    @php
                        $detailFields = [
                            'clock_in' => ['label' => 'Masuk', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'break_out' => ['label' => 'Istirahat', 'icon' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'],
                            'break_in' => ['label' => 'Masuk Istirahat', 'icon' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'],
                            'clock_out' => ['label' => 'Pulang', 'icon' => 'M5 10l7-7m0 0l7 7m-7-7v18'],
                            'overtime_in' => ['label' => 'Lembur Masuk', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'overtime_out' => ['label' => 'Lembur Pulang', 'icon' => 'M5 10l7-7m0 0l7 7m-7-7v18'],
                        ];
                    @endphp
                    @foreach($detailFields as $field => $meta)
                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl px-3 py-2.5 flex items-center gap-2.5">
                        <svg class="w-4 h-4 {{ $att->$field ? 'text-blue-500' : 'text-gray-300 dark:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}"/></svg>
                        <div>
                            <p class="text-[10px] text-gray-400">{{ $meta['label'] }}</p>
                            <p class="text-sm font-semibold {{ $att->$field ? 'text-gray-900 dark:text-white' : 'text-gray-300 dark:text-gray-600' }}">
                                {{ $att->$field ? $att->$field->format('H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($att->notes)
                <div class="mt-2 text-[11px] text-gray-500 italic">Catatan: {{ $att->notes }}</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $attendances->appends(request()->query())->links() }}
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        <p class="text-sm text-gray-500">Belum ada data absensi</p>
    </div>
    @endif
</div>
@endsection