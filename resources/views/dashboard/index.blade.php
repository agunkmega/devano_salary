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

    <div x-data="chartView()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Grafik Kehadiran</h3>
                <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button @click="switchView('daily')" class="px-4 py-1.5 text-sm font-medium transition-colors" :class="view === 'daily' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'">Harian</button>
                    <button @click="switchView('monthly')" class="px-4 py-1.5 text-sm font-medium transition-colors" :class="view === 'monthly' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'">Bulanan</button>
                </div>
            </div>
            <div class="relative" style="height:300px">
                <canvas x-ref="chart"></canvas>
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
                const data = this.view === 'daily' ? this.dailyData : this.monthlyData;
                const labelKey = this.view === 'daily' ? 'date' : 'month';
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d[labelKey]),
                        datasets: [
                            { label: 'Hadir', data: data.map(d => d.hadir), backgroundColor: '#10b981', borderRadius: 4 },
                            { label: 'Terlambat', data: data.map(d => d.terlambat), backgroundColor: '#f59e0b', borderRadius: 4 },
                            { label: 'Izin', data: data.map(d => d.izin), backgroundColor: '#3b82f6', borderRadius: 4 },
                            { label: 'Sakit', data: data.map(d => d.sakit), backgroundColor: '#8b5cf6', borderRadius: 4 },
                            { label: 'Cuti', data: data.map(d => d.cuti), backgroundColor: '#6366f1', borderRadius: 4 },
                            { label: 'Alpha', data: data.map(d => d.alpha), backgroundColor: '#ef4444', borderRadius: 4 },
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 11 } } } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#e5e7eb' } }
                        }
                    }
                });
            }
        }));
    });
</script>
@endpush