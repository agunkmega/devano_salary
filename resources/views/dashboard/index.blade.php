@extends('layouts.admin')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview & statistics')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.5.1/moment.min.js"></script>
<style>
    #calendar {
        -webkit-transform: translate3d(0, 0, 0);
        -moz-transform: translate3d(0, 0, 0);
        transform: translate3d(0, 0, 0);
        width: 100%;
        margin: 0 auto;
        height: auto;
        min-height: 480px;
        overflow: hidden;
    }
    #calendar *, #calendar *:before, #calendar *:after {
        -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;
    }
    #calendar .header {
        height: 50px;
        width: 100%;
        background: rgba(66, 66, 66, 1);
        text-align: center;
        position: relative;
        z-index: 100;
    }
    #calendar .header h1 {
        margin: 0;
        padding: 0;
        font-size: 20px;
        line-height: 50px;
        font-weight: 100;
        letter-spacing: 1px;
    }
    #calendar .left, #calendar .right {
        position: absolute;
        width: 0px;
        height: 0px;
        border-style: solid;
        top: 50%;
        margin-top: -7.5px;
        cursor: pointer;
    }
    #calendar .left {
        border-width: 7.5px 10px 7.5px 0;
        border-color: transparent rgba(160, 159, 160, 1) transparent transparent;
        left: 20px;
    }
    #calendar .right {
        border-width: 7.5px 0 7.5px 10px;
        border-color: transparent transparent transparent rgba(160, 159, 160, 1);
        right: 20px;
    }
    #calendar .month { opacity: 0; }
    #calendar .month.new {
        -webkit-animation: fadeIn 1s ease-out;
        opacity: 1;
    }
    #calendar .month.in.next {
        -webkit-animation: moveFromTopFadeMonth .4s ease-out;
        animation: moveFromTopFadeMonth .4s ease-out;
        opacity: 1;
    }
    #calendar .month.out.next {
        -webkit-animation: moveToTopFadeMonth .4s ease-in;
        animation: moveToTopFadeMonth .4s ease-in;
        opacity: 1;
    }
    #calendar .month.in.prev {
        -webkit-animation: moveFromBottomFadeMonth .4s ease-out;
        animation: moveFromBottomFadeMonth .4s ease-out;
        opacity: 1;
    }
    #calendar .month.out.prev {
        -webkit-animation: moveToBottomFadeMonth .4s ease-in;
        animation: moveToBottomFadeMonth .4s ease-in;
        opacity: 1;
    }
    #calendar .week { background: #4A4A4A; display: flex; }
    #calendar .day {
        display: inline-block;
        width: 14.28%;
        flex: 1 1 14.28%;
        min-width: 0;
        padding: 8px 4px;
        text-align: center;
        vertical-align: top;
        cursor: pointer;
        background: #4A4A4A;
        position: relative;
        z-index: 100;
    }
    #calendar .day.other { color: rgba(255, 255, 255, .3); }
    #calendar .day.today { color: rgba(156, 202, 235, 1); }
    #calendar .day-name {
        font-size: 9px;
        text-transform: uppercase;
        margin-bottom: 5px;
        color: rgba(255, 255, 255, .5);
        letter-spacing: .7px;
    }
    #calendar .day-number { font-size: 22px; letter-spacing: 1.5px; }
    #calendar .day .day-events {
        list-style: none;
        margin-top: 3px;
        text-align: center;
        height: 12px;
        line-height: 6px;
        overflow: hidden;
    }
    #calendar .day .day-events span {
        vertical-align: top;
        display: inline-block;
        padding: 0;
        margin: 0;
        width: 5px;
        height: 5px;
        line-height: 5px;
        margin: 0 1px;
    }
    #calendar .blue { background: rgba(156, 202, 235, 1); }
    #calendar .orange { background: rgba(247, 167, 0, 1); }
    #calendar .green { background: rgba(153, 198, 109, 1); }
    #calendar .yellow { background: rgba(249, 233, 0, 1); }
    #calendar .red { background: rgba(240, 100, 100, 1); }
    #calendar .purple { background: rgba(180, 140, 230, 1); }
    #calendar .details {
        position: relative;
        width: 100%;
        height: 75px;
        background: rgba(164, 164, 164, 1);
        margin-top: 5px;
        border-radius: 4px;
    }
    #calendar .details.in {
        -webkit-animation: moveFromTopFade .5s ease both;
        animation: moveFromTopFade .5s ease both;
    }
    #calendar .details.out {
        -webkit-animation: moveToTopFade .5s ease both;
        animation: moveToTopFade .5s ease both;
    }
    #calendar .arrow {
        position: absolute;
        top: -5px;
        left: 50%;
        margin-left: -2px;
        width: 0px;
        height: 0px;
        border-style: solid;
        border-width: 0 5px 5px 5px;
        border-color: transparent transparent rgba(164, 164, 164, 1) transparent;
        transition: all 0.7s ease;
    }
    #calendar .events {
        height: 75px;
        padding: 7px 0;
        overflow-y: auto;
        overflow-x: hidden;
    }
    #calendar .events.in {
        -webkit-animation: fadeIn .3s ease both;
        animation: fadeIn .3s ease both;
        -webkit-animation-delay: .3s;
        animation-delay: .3s;
    }
    #calendar .details.out .events {
        -webkit-animation: fadeOutShink .4s ease both;
        animation: fadeOutShink .4s ease both;
    }
    #calendar .events.out {
        -webkit-animation: fadeOut .3s ease both;
        animation: fadeOut .3s ease both;
    }
    #calendar .event {
        font-size: 16px;
        line-height: 22px;
        letter-spacing: .5px;
        padding: 2px 16px;
        vertical-align: top;
        color: rgba(255, 255, 255, 1);
    }
    #calendar .event.empty { color: #eee; }
    #calendar .event-category {
        height: 10px;
        width: 10px;
        display: inline-block;
        margin: 6px 0 0;
        vertical-align: top;
    }
    #calendar .event span { display: inline-block; padding: 0 0 0 7px; }
    #calendar .legend {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 30px;
        background: rgba(60, 60, 60, 1);
        line-height: 30px;
    }
    #calendar .entry {
        position: relative;
        padding: 0 0 0 25px;
        font-size: 13px;
        display: inline-block;
        line-height: 30px;
        background: transparent;
    }
    #calendar .entry:after {
        position: absolute;
        content: '';
        height: 5px;
        width: 5px;
        top: 12px;
        left: 14px;
    }
    #calendar .entry.blue:after { background: rgba(156, 202, 235, 1); }
    #calendar .entry.orange:after { background: rgba(247, 167, 0, 1); }
    #calendar .entry.green:after { background: rgba(153, 198, 109, 1); }
    #calendar .entry.yellow:after { background: rgba(249, 233, 0, 1); }
    #calendar .entry.red:after { background: rgba(240, 100, 100, 1); }
    #calendar .entry.purple:after { background: rgba(180, 140, 230, 1); }
    #calendar .entry:last-child { margin-right: 15px; }

    @-webkit-keyframes moveFromTopFade {
      from { opacity: .3; height:0px; margin-top:0px; -webkit-transform: translateY(-100%); }
    }
    @keyframes moveFromTopFade {
      from { height:0px; margin-top:0px; transform: translateY(-100%); }
    }
    @-webkit-keyframes moveToTopFade {
      to { opacity: .3; height:0px; margin-top:0px; opacity: 0.3; -webkit-transform: translateY(-100%); }
    }
    @keyframes moveToTopFade {
      to { height:0px; transform: translateY(-100%); }
    }
    @-webkit-keyframes moveToTopFadeMonth {
      to { opacity: 0; -webkit-transform: translateY(-30%) scale(.95); }
    }
    @keyframes moveToTopFadeMonth {
      to { opacity: 0; -webkit-transform: translateY(-30%); }
    }
    @-webkit-keyframes moveFromTopFadeMonth {
      from { opacity: 0; -webkit-transform: translateY(30%) scale(.95); }
    }
    @keyframes moveFromTopFadeMonth {
      from { opacity: 0; -webkit-transform: translateY(30%); }
    }
    @-webkit-keyframes moveToBottomFadeMonth {
      to { opacity: 0; -webkit-transform: translateY(30%) scale(.95); }
    }
    @keyframes moveToBottomFadeMonth {
      to { opacity: 0; -webkit-transform: translateY(30%); }
    }
    @-webkit-keyframes moveFromBottomFadeMonth {
      from { opacity: 0; -webkit-transform: translateY(-30%) scale(.95); }
    }
    @keyframes moveFromBottomFadeMonth {
      from { opacity: 0; -webkit-transform: translateY(-30%); }
    }
    @-webkit-keyframes fadeIn { from { opacity: 0; } }
    @keyframes fadeIn { from { opacity: 0; } }
    @-webkit-keyframes fadeOut { to { opacity: 0; } }
    @keyframes fadeOut { to { opacity: 0; } }
    @-webkit-keyframes fadeOutShink { to { opacity: 0; padding: 0px; height: 0px; } }
    @keyframes fadeOutShink { to { opacity: 0; padding: 0px; height: 0px; } }
</style>
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
        <div x-data="dashboardCalendar()" class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 flex flex-col items-center justify-center overflow-hidden">
            <div id="calendar"></div>
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
    !function() {
        var today = moment();

        function Calendar(selector, events, currentMonth) {
            this.el = document.querySelector(selector);
            this.events = events;
            this.current = currentMonth ? moment(currentMonth + '-01', 'YYYY-MM-DD') : moment().date(1);
            this.draw();
            var current = document.querySelector('.today');
            if (current) {
                var self = this;
                window.setTimeout(function() {
                    self.openDay(current);
                }, 500);
            }
        }

        Calendar.prototype.draw = function() {
            this.drawHeader();
            this.drawMonth();
            this.drawLegend();
        }

        Calendar.prototype.drawHeader = function() {
            var self = this;
            if (!this.header) {
                this.header = createElement('div', 'header');
                this.header.className = 'header';
                this.title = createElement('h1');
                var right = createElement('div', 'right');
                right.addEventListener('click', function() { self.nextMonth(); });
                var left = createElement('div', 'left');
                left.addEventListener('click', function() { self.prevMonth(); });
                this.header.appendChild(this.title);
                this.header.appendChild(right);
                this.header.appendChild(left);
                this.el.appendChild(this.header);
            }
            this.title.innerHTML = this.current.format('MMMM YYYY');
        }

        Calendar.prototype.drawMonth = function() {
            var self = this;

            // Assign event date (no randomness — use real date if provided)
            this.events.forEach(function(ev) {
                if (ev.date) {
                    ev.mdate = moment(ev.date, 'YYYY-MM-DD');
                }
            });

            if (this.month) {
                this.oldMonth = this.month;
                this.oldMonth.className = 'month out ' + (self.next ? 'next' : 'prev');
                this.oldMonth.addEventListener('webkitAnimationEnd', function() {
                    self.oldMonth.parentNode.removeChild(self.oldMonth);
                    self.month = createElement('div', 'month');
                    self.backFill();
                    self.currentMonth();
                    self.fowardFill();
                    self.el.appendChild(self.month);
                    window.setTimeout(function() {
                        self.month.className = 'month in ' + (self.next ? 'next' : 'prev');
                    }, 16);
                });
            } else {
                this.month = createElement('div', 'month');
                this.el.appendChild(this.month);
                this.backFill();
                this.currentMonth();
                this.fowardFill();
                this.month.className = 'month new';
            }
        }

        Calendar.prototype.backFill = function() {
            var clone = this.current.clone();
            var dayOfWeek = clone.day();
            if (!dayOfWeek) { return; }
            clone.subtract('days', dayOfWeek + 1);
            for (var i = dayOfWeek; i > 0; i--) {
                this.drawDay(clone.add('days', 1));
            }
        }

        Calendar.prototype.fowardFill = function() {
            var clone = this.current.clone().add('months', 1).subtract('days', 1);
            var dayOfWeek = clone.day();
            if (dayOfWeek === 6) { return; }
            for (var i = dayOfWeek; i < 6; i++) {
                this.drawDay(clone.add('days', 1));
            }
        }

        Calendar.prototype.currentMonth = function() {
            var clone = this.current.clone();
            while (clone.month() === this.current.month()) {
                this.drawDay(clone);
                clone.add('days', 1);
            }
        }

        Calendar.prototype.getWeek = function(day) {
            if (!this.week || day.day() === 0) {
                this.week = createElement('div', 'week');
                this.month.appendChild(this.week);
            }
        }

        Calendar.prototype.drawDay = function(day) {
            var self = this;
            this.getWeek(day);
            var outer = createElement('div', this.getDayClass(day));
            outer.addEventListener('click', function() {
                self.openDay(this);
            });
            var name = createElement('div', 'day-name', day.format('ddd'));
            var number = createElement('div', 'day-number', day.format('DD'));
            var events = createElement('div', 'day-events');
            this.drawEvents(day, events);
            outer.appendChild(name);
            outer.appendChild(number);
            outer.appendChild(events);
            this.week.appendChild(outer);
        }

        Calendar.prototype.drawEvents = function(day, element) {
            if (day.month() === this.current.month()) {
                var todaysEvents = this.events.reduce(function(memo, ev) {
                    if (ev.mdate && ev.mdate.isSame(day, 'day')) {
                        memo.push(ev);
                    }
                    return memo;
                }, []);
                todaysEvents.forEach(function(ev) {
                    var evSpan = createElement('span', ev.color);
                    element.appendChild(evSpan);
                });
            }
        }

        Calendar.prototype.getDayClass = function(day) {
            classes = ['day'];
            if (day.month() !== this.current.month()) {
                classes.push('other');
            } else if (today.isSame(day, 'day')) {
                classes.push('today');
            }
            return classes.join(' ');
        }

        Calendar.prototype.openDay = function(el) {
            var details, arrow;
            var dayNumber = +el.querySelectorAll('.day-number')[0].innerText || +el.querySelectorAll('.day-number')[0].textContent;
            var day = this.current.clone().date(dayNumber);

            var currentOpened = document.querySelector('.details');
            if (currentOpened && currentOpened.parentNode === el.parentNode) {
                details = currentOpened;
                arrow = document.querySelector('.arrow');
            } else {
                if (currentOpened) {
                    currentOpened.addEventListener('webkitAnimationEnd', function() {
                        currentOpened.parentNode.removeChild(currentOpened);
                    });
                    currentOpened.addEventListener('oanimationend', function() {
                        currentOpened.parentNode.removeChild(currentOpened);
                    });
                    currentOpened.addEventListener('msAnimationEnd', function() {
                        currentOpened.parentNode.removeChild(currentOpened);
                    });
                    currentOpened.addEventListener('animationend', function() {
                        currentOpened.parentNode.removeChild(currentOpened);
                    });
                    currentOpened.className = 'details out';
                }
                details = createElement('div', 'details in');
                var arrow = createElement('div', 'arrow');
                details.appendChild(arrow);
                el.parentNode.appendChild(details);
            }

            var todaysEvents = this.events.reduce(function(memo, ev) {
                if (ev.mdate && ev.mdate.isSame(day, 'day')) {
                    memo.push(ev);
                }
                return memo;
            }, []);

            this.renderEvents(todaysEvents, details);
            arrow.style.left = (el.offsetLeft - el.parentNode.offsetLeft + el.offsetWidth / 2) + 'px';
        }

        Calendar.prototype.renderEvents = function(events, ele) {
            var currentWrapper = ele.querySelector('.events');
            var wrapper = createElement('div', 'events in' + (currentWrapper ? ' new' : ''));
            events.forEach(function(ev) {
                var div = createElement('div', 'event');
                var square = createElement('div', 'event-category ' + ev.color);
                var span = createElement('span', '', ev.eventName);
                div.appendChild(square);
                div.appendChild(span);
                wrapper.appendChild(div);
            });
            if (!events.length) {
                var div = createElement('div', 'event empty');
                var span = createElement('span', '', 'No Events');
                div.appendChild(span);
                wrapper.appendChild(div);
            }
            if (currentWrapper) {
                currentWrapper.className = 'events out';
                currentWrapper.addEventListener('webkitAnimationEnd', function() {
                    currentWrapper.parentNode.removeChild(currentWrapper);
                    ele.appendChild(wrapper);
                });
                currentWrapper.addEventListener('oanimationend', function() {
                    currentWrapper.parentNode.removeChild(currentWrapper);
                    ele.appendChild(wrapper);
                });
                currentWrapper.addEventListener('msAnimationEnd', function() {
                    currentWrapper.parentNode.removeChild(currentWrapper);
                    ele.appendChild(wrapper);
                });
                currentWrapper.addEventListener('animationend', function() {
                    currentWrapper.parentNode.removeChild(currentWrapper);
                    ele.appendChild(wrapper);
                });
            } else {
                ele.appendChild(wrapper);
            }
        }

        Calendar.prototype.drawLegend = function() {
            var legend = createElement('div', 'legend');
            var calendars = this.events.map(function(e) {
                return e.calendar + '|' + e.color;
            }).reduce(function(memo, e) {
                if (memo.indexOf(e) === -1) {
                    memo.push(e);
                }
                return memo;
            }, []).forEach(function(e) {
                var parts = e.split('|');
                var entry = createElement('span', 'entry ' + parts[1], parts[0]);
                legend.appendChild(entry);
            });
            this.el.appendChild(legend);
        }

        Calendar.prototype.nextMonth = function() {
            window.location.href = this.gotoMonth(1);
        }

        Calendar.prototype.prevMonth = function() {
            window.location.href = this.gotoMonth(-1);
        }

        Calendar.prototype.gotoMonth = function(delta) {
            var d = this.current.clone().add('months', delta);
            var ym = d.format('YYYY-MM');
            var url = new URL(window.location.href);
            url.searchParams.set('cal_month', ym);
            return url.toString();
        }

        window.Calendar = Calendar;

        function createElement(tagName, className, innerText) {
            var ele = document.createElement(tagName);
            if (className) {
                ele.className = className;
            }
            if (innerText) {
                ele.innerText = ele.textContent = innerText;
            }
            return ele;
        }
    }();

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
            init() {
                const calData = @json($calendarData);
                const events = [];

                Object.entries(calData.holidays || {}).forEach(([date, h]) => {
                    events.push({ date: date, eventName: h.name, calendar: 'Libur', color: 'red' });
                });

                Object.entries(calData.leaves || {}).forEach(([date, list]) => {
                    list.forEach(lv => {
                        let color = 'purple';
                        if ((lv.type || '').toLowerCase().includes('izin')) color = 'orange';
                        else if ((lv.type || '').toLowerCase().includes('sakit')) color = 'green';
                        events.push({ date: date, eventName: lv.name + ' · ' + lv.type, calendar: 'Cuti', color: color });
                    });
                });

                Object.entries(calData.attendance || {}).forEach(([date, att]) => {
                    if (att.total_hadir > 0) events.push({ date: date, eventName: att.total_hadir + ' hadir', calendar: 'Hadir', color: 'blue' });
                    if (att.alpha > 0) events.push({ date: date, eventName: att.alpha + ' alpha', calendar: 'Alpha', color: 'yellow' });
                });

                this.cal = new Calendar('#calendar', events, calData.month);
            }
        }));
    });
</script>
@endpush