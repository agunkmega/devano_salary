@extends('layouts.admin')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview & statistics')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endpush

@section('page-content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Selamat datang, {{ auth()->user()->name ?? 'User' }}!</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="hidden sm:flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Periode:</span>
            <form method="GET" id="period-form">
                <select name="period" onchange="document.getElementById('period-form').submit()" class="text-sm border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <option value="this-month" {{ request('period', 'this-month') == 'this-month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="last-month" {{ request('period') == 'last-month' ? 'selected' : '' }}>Bulan Lalu</option>
                    <option value="this-year" {{ request('period') == 'this-year' ? 'selected' : '' }}>Tahun Ini</option>
                </select>
            </form>
        </div>
    </div>

    @if($todayBirthdays->count() > 0)
    <div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-white">Selamat Ulang Tahun!</p>
            <div class="mt-1 space-y-0.5">
                @foreach($todayBirthdays as $b)
                <p class="text-sm text-white/90">{{ $b->full_name }}</p>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($thisMonthBirthdays->count() > 0)
    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-white">Ultah Bulan Ini</p>
            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-0.5">
                @foreach($thisMonthBirthdays as $b)
                <span class="text-sm text-white/80">&#8226; {{ $b->full_name }} ({{ \Carbon\Carbon::parse($b->birth_date)->format('d M') }})</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <x-stats-card icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />' label="Total Pegawai" value="{{ $totalEmployees }}" color="blue" />
        <x-stats-card icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />' label="Hadir Hari Ini" value="{{ $todayAttendance }}" color="green" />
        <x-stats-card icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />' label="Terlambat" value="{{ $lateToday }}" color="orange" />
        <x-stats-card icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />' label="Cuti" value="{{ $onLeaveToday }}" color="purple" />
        <x-stats-card icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />' label="Tidak Hadir" value="{{ $absentToday }}" color="red" />
        <x-stats-card icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />' label="Total Gaji Bulanan" value="Rp {{ number_format($monthlyPayroll, 0, ',', '.') }}" color="indigo" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div x-data="dashboardCalendar()" class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kalender Kehadiran</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $calendarData['month_label'] }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="navigate(-1)" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" @click="navigate(1)" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <button type="button" @click="navigate(0)" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Bulan Ini</button>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-1 text-center mb-2">
                @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $dn)
                <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 py-1.5">{{ $dn }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-1">
                @php
                    $calMonth = Carbon\Carbon::parse($calendarData['month']);
                    $calStart = $calMonth->copy()->startOfMonth()->startOfDay();
                    $calEnd = $calMonth->copy()->endOfMonth();
                    $calOffset = $calStart->dayOfWeek;
                @endphp
                @for($i = 0; $i < $calOffset; $i++)
                <div class="min-h-[76px] rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/40"></div>
                @endfor
                @for($d = 1; $d <= $calMonth->daysInMonth; $d++)
                @php
                    $date = $calStart->copy()->setDay($d);
                    $key = $date->format('Y-m-d');
                    $isHoliday = isset($calendarData['holidays'][$key]);
                    $holidayName = $isHoliday ? $calendarData['holidays'][$key]['name'] : null;
                    $leaves = $calendarData['leaves'][$key] ?? [];
                    $att = $calendarData['attendance'][$key] ?? null;
                    $isSunday = $date->dayOfWeek === 0;
                    $isToday = $key === $calendarData['today'];
                    $isPast = $key < $calendarData['today'];

                    $events = [];
                    if ($isHoliday) {
                        $events[] = ['label' => $holidayName, 'color' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'];
                    }
                    if (count($leaves) > 0) {
                        foreach ($leaves as $lv) {
                            $events[] = ['label' => $lv['name'] . ' · ' . $lv['type'], 'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300'];
                        }
                    }
                    if ($att) {
                        if ($att['total_hadir'] > 0) {
                            $events[] = ['label' => $att['total_hadir'] . ' hadir', 'color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'];
                        }
                        if ($att['izin'] > 0) {
                            $events[] = ['label' => $att['izin'] . ' izin', 'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'];
                        }
                        if ($att['sakit'] > 0) {
                            $events[] = ['label' => $att['sakit'] . ' sakit', 'color' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300'];
                        }
                        if ($att['alpha'] > 0) {
                            $events[] = ['label' => $att['alpha'] . ' alpha', 'color' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300'];
                        }
                    }
                    $showEvents = array_slice($events, 0, 3);
                    $moreCount = count($events) - count($showEvents);
                @endphp
                <button type="button" @click="openDay('{{ $key }}')" class="min-h-[76px] rounded-lg border p-1.5 flex flex-col items-stretch gap-1 text-left transition-colors relative
                    {{ $isHoliday ? 'bg-red-50/70 dark:bg-red-900/10 border-red-200 dark:border-red-800' : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700' }}
                    hover:border-blue-300 dark:hover:border-blue-700">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold {{ $isToday ? 'bg-blue-600 text-white w-5 h-5 flex items-center justify-center rounded-full' : ($isSunday || $isHoliday ? 'text-red-500 dark:text-red-400' : ($isPast ? 'text-gray-300 dark:text-gray-500' : 'text-gray-700 dark:text-gray-300')) }}">{{ $d }}</span>
                        @if($att && ($att['izin'] > 0 || $att['sakit'] > 0 || $att['cuti'] > 0 || $att['alpha'] > 0 || $att['total_hadir'] > 0))
                        <span class="text-[9px] text-gray-400 dark:text-gray-500">{{ $att['total_hadir'] }}/{{ $isHoliday || (!$isPast && $isSunday) ? '-' : $calendarData['total_employees'] }}</span>
                        @elseif($isHoliday)
                        <span class="text-[9px] text-red-400">Libur</span>
                        @endif
                    </div>
                    <div class="flex flex-col gap-0.5 flex-1">
                        @foreach($showEvents as $ev)
                        <span class="text-[9px] leading-tight px-1 py-0.5 rounded truncate {{ $ev['color'] }}">{{ $ev['label'] }}</span>
                        @endforeach
                        @if($moreCount > 0)
                        <span class="text-[9px] text-gray-400 dark:text-gray-500 px-1">+{{ $moreCount }} lainnya</span>
                        @endif
                    </div>
                </button>
                @endfor
            </div>

            <div class="flex flex-wrap items-center gap-4 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50 text-xs">
                <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Hadir</span>
                <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Izin</span>
                <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span> Sakit</span>
                <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Cuti</span>
                <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Libur Nasional</span>
                <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Alpha</span>
            </div>

            {{-- Day detail modal --}}
            <div x-show="selectedDay" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" @click.self="selectedDay = false">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white" x-text="detailTitle"></h4>
                        <button type="button" @click="selectedDay = false" class="w-8 h-8 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4 text-sm">
                        <template x-if="detailHoliday">
                            <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300">
                                <p class="font-semibold">Libur Nasional</p>
                                <p class="mt-0.5" x-text="detailHoliday"></p>
                            </div>
                        </template>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-center">
                                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400" x-text="detailHadir"></p>
                                <p class="text-xs text-emerald-700 dark:text-emerald-300">Pegawai Hadir</p>
                            </div>
                            <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-center">
                                <p class="text-lg font-bold text-rose-600 dark:text-rose-400" x-text="detailAlpha"></p>
                                <p class="text-xs text-rose-700 dark:text-rose-300">Tidak Hadir</p>
                            </div>
                        </div>
                        <div x-show="detailLeaves.length > 0">
                            <p class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Pegawai Cuti/Izin:</p>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto">
                                <template x-for="(lv, i) in detailLeaves" :key="i">
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                        <span class="text-gray-800 dark:text-gray-200" x-text="lv.name"></span>
                                        <span class="text-xs text-purple-600 dark:text-purple-400" x-text="lv.type"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <p x-show="detailLeaves.length === 0 && !detailHoliday && detailHadir === 0" class="text-gray-400 text-center py-4">Tidak ada aktivitas pada tanggal ini</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ringkasan Kasbon</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Outstanding</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($cashAdvanceSummary['total_outstanding'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Jumlah Pegawai</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $cashAdvanceSummary['count'] }} orang</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistik Departemen</h3>
                <div class="space-y-3">
                    @foreach($departmentStats as $dept)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $dept->name }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $dept->employees_count }} pegawai</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div x-data="chartView()" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Grafik Kehadiran</h3>
            <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button @click="switchView('daily')" class="px-4 py-1.5 text-sm font-medium transition-colors" :class="view === 'daily' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'">Harian</button>
                <button @click="switchView('monthly')" class="px-4 py-1.5 text-sm font-medium transition-colors" :class="view === 'monthly' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'">Bulanan</button>
            </div>
        </div>
        <div class="relative" style="height:230px">
            <canvas x-ref="chart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Absensi Terbaru</h3>
                <a href="{{ route('admin.attendances.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-2 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                            <th class="text-left py-3 px-2 text-gray-500 dark:text-gray-400 font-medium">Masuk</th>
                            <th class="text-left py-3 px-2 text-gray-500 dark:text-gray-400 font-medium">Keluar</th>
                            <th class="text-left py-3 px-2 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $att)
                        <tr class="border-b border-gray-100 dark:border-gray-700/50">
                            <td class="py-3 px-2 text-gray-900 dark:text-white">{{ $att->employee?->full_name ?? 'N/A' }}</td>
                            <td class="py-3 px-2 text-gray-600 dark:text-gray-400">{{ $att->clock_in ? Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }}</td>
                            <td class="py-3 px-2 text-gray-600 dark:text-gray-400">{{ $att->clock_out ? Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }}</td>
                            <td class="py-3 px-2">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                    @if($att->status == 'hadir') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400
                                    @elseif($att->status == 'terlambat') bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400
                                    @elseif(in_array($att->status, ['izin','sakit'])) bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400
                                    @elseif($att->status == 'cuti') bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400
                                    @else bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                    @endif">
                                    {{ ucfirst($att->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data absensi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cuti Menunggu Persetujuan</h3>
                <a href="{{ route('admin.leaves.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @php
                    $pendingLeaves = \App\Models\Leave::with('employee')->where('status', 'pending')->latest()->take(5)->get();
                @endphp
                @forelse($pendingLeaves as $leave)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-semibold text-sm">
                        {{ substr($leave->employee?->full_name ?? '?', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $leave->employee?->full_name ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $leave->leaveType?->name ?? 'Cuti' }} - {{ $leave->total_days }} hari</p>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">Pending</span>
                </div>
                @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada cuti pending</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chartView', () => ({
            view: 'daily',
            chart: null,
            dailyData: @json($dailyChartData),
            monthlyData: @json($chartData),
            init() {
                this.$nextTick(() => this.renderChart());
            },
            switchView(v) {
                this.view = v;
                this.$nextTick(() => this.renderChart());
            },
            renderChart() {
                if (this.chart) this.chart.destroy();
                const ctx = this.$refs.chart.getContext('2d');
                const isDark = document.documentElement.classList.contains('dark');
                const data = this.view === 'daily' ? this.dailyData : this.monthlyData;
                const labelKey = this.view === 'daily' ? 'date' : 'month';
                const colors = {
                    hadir: { solid: '#10b981', gradient: (c) => { const g = c.createLinearGradient(0,0,0,300); g.addColorStop(0,'#34d399'); g.addColorStop(1,'#10b981'); return g; } },
                    terlambat: { solid: '#f59e0b', gradient: (c) => { const g = c.createLinearGradient(0,0,0,300); g.addColorStop(0,'#fbbf24'); g.addColorStop(1,'#f59e0b'); return g; } },
                    izin: { solid: '#3b82f6', gradient: (c) => { const g = c.createLinearGradient(0,0,0,300); g.addColorStop(0,'#60a5fa'); g.addColorStop(1,'#3b82f6'); return g; } },
                    sakit: { solid: '#8b5cf6', gradient: (c) => { const g = c.createLinearGradient(0,0,0,300); g.addColorStop(0,'#a78bfa'); g.addColorStop(1,'#8b5cf6'); return g; } },
                    cuti: { solid: '#6366f1', gradient: (c) => { const g = c.createLinearGradient(0,0,0,300); g.addColorStop(0,'#818cf8'); g.addColorStop(1,'#6366f1'); return g; } },
                    alpha: { solid: '#ef4444', gradient: (c) => { const g = c.createLinearGradient(0,0,0,300); g.addColorStop(0,'#f87171'); g.addColorStop(1,'#ef4444'); return g; } },
                };
                const datasets = ['Hadir','Terlambat','Izin','Sakit','Cuti','Alpha'].map((label, i) => {
                    const key = Object.keys(colors)[i];
                    return {
                        label,
                        data: data.map(d => d[key]),
                        backgroundColor: (c) => colors[key].gradient(c.chart.ctx),
                        borderColor: colors[key].solid,
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                        hoverBorderWidth: 2,
                        hoverBorderColor: colors[key].solid,
                    };
                });
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: { labels: data.map(d => d[labelKey]), datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 600, easing: 'easeOutQuart' },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    borderRadius: 3,
                                    padding: 16,
                                    font: { size: 11, family: "'Inter','Figtree',sans-serif" },
                                    color: isDark ? '#cbd5e1' : '#64748b',
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                }
                            },
                            tooltip: {
                                backgroundColor: isDark ? '#1e293b' : '#fff',
                                titleColor: isDark ? '#f1f5f9' : '#1e293b',
                                bodyColor: isDark ? '#cbd5e1' : '#475569',
                                borderColor: isDark ? '#334155' : '#e2e8f0',
                                borderWidth: 1,
                                cornerRadius: 12,
                                padding: 12,
                                boxPadding: 6,
                                usePointStyle: true,
                                bodyFont: { size: 12, family: "'Inter','Figtree',sans-serif" },
                                titleFont: { size: 12, weight: '600', family: "'Inter','Figtree',sans-serif" },
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 10, family: "'Inter','Figtree',sans-serif" },
                                    color: isDark ? '#94a3b8' : '#94a3b8',
                                    maxRotation: this.view === 'daily' ? 45 : 0,
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: { size: 10, family: "'Inter','Figtree',sans-serif" },
                                    color: isDark ? '#94a3b8' : '#94a3b8',
                                },
                                grid: {
                                    color: isDark ? 'rgba(148,163,184,0.1)' : 'rgba(148,163,184,0.2)',
                                    drawBorder: false,
                                }
                            }
                        }
                    }
                });
            }
        }));

        Alpine.data('dashboardCalendar', () => ({
            data: @json($calendarData),
            selectedDay: false,
            insets: {},
            init() {
                this.insets = this.data;
            },
            navigate(delta) {
                const [year, month] = this.data.month.split('-').map(Number);
                const d = new Date(year, month - 1 + delta, 1);
                const ym = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
                window.location.href = this.withCalendar(ym);
            },
            withCalendar(ym) {
                const url = new URL(window.location.href);
                url.searchParams.set('cal_month', ym);
                return url.toString();
            },
            get detailTitle() {
                if (!this.selectedDay) return '';
                const d = new Date(this.selectedDay + 'T00:00:00');
                return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            },
            get detailHoliday() {
                return this.data.holidays[this.selectedDay]?.name || '';
            },
            get detailLeaves() {
                return this.data.leaves[this.selectedDay] || [];
            },
            get detailHadir() {
                return this.data.attendance[this.selectedDay]?.total_hadir ?? 0;
            },
            get detailAlpha() {
                const att = this.data.attendance[this.selectedDay];
                if (!att) return 0;
                const hadir = att.hadir + att.terlambat;
                const onLeave = att.izin + att.sakit + att.cuti;
                return Math.max(0, this.data.total_employees - hadir - onLeave);
            },
            openDay(key) {
                this.selectedDay = key;
            }
        }));
    });
</script>
@endpush