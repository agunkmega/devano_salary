@extends('layouts.admin')

@section('page-title', 'Laporan Absensi')
@section('page-subtitle', 'Rekap kehadiran karyawan')

@section('page-content')
<div class="space-y-6">
    <div class="no-print bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <script>const employeeData = @js($employees->map(fn($e) => ['id' => $e->id, 'name' => $e->full_name])->values());</script>
        <form method="GET" action="{{ route('admin.reports.attendance') }}" class="flex flex-wrap gap-4 items-end" id="report-form">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Departemen</label>
                <select name="department_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jabatan</label>
                <select name="position_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($positions as $pos)
                    <option value="{{ $pos->id }}" {{ request('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                    @endforeach
                </select>
            </div>
            <div x-data="employeeSearch()" class="relative">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pegawai</label>
                <input type="text" x-model="query" @input="open = true" @focus="open = true" @click.away="open = false" @keydown.escape="open = false" placeholder="Cari pegawai..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 w-48 focus:ring-2 focus:ring-blue-500">
                <input type="hidden" name="employee_id" :value="selectedId" id="search-employee-id">
                <div x-show="open && query.length > 0" class="absolute z-10 mt-1 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                    <template x-for="emp in filtered" :key="emp.id">
                        <button type="button" @click="select(emp)" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white" x-text="emp.name"></button>
                    </template>
                    <p x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan</p>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis Pegawai</label>
                <select name="employee_type" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="bulanan" {{ request('employee_type') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="harian" {{ request('employee_type') == 'harian' ? 'selected' : '' }}>Harian</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Generate</button>
        </form>
    </div>

    <div class="no-print grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $summary['hadir'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Hadir</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $summary['terlambat'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Terlambat</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $summary['izin'] + $summary['sakit'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Izin / Sakit</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $summary['alpha'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Alpha</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Resume Absensi</h3>
            <div class="flex gap-2">
            <a href="{{ route('admin.reports.attendance-excel', request()->all()) }}" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Excel
            </a>
            <button onclick="window.print()" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Jabatan</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Hadir</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Terlambat</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Istirahat Lebih</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Pulang Awal</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Cuti</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Alfa</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Sakit</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeeSummaries as $r)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-3 text-gray-900 dark:text-white font-medium">{{ $r['nama'] }}</td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">{{ $r['jabatan'] }}</td>
                        <td class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400 font-medium">{{ $r['hadir'] }}</td>
                        <td class="py-3 px-3 text-center {{ $r['terlambat'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $r['telat_hari'] }}x
                            @if($r['telat_menit'] > 0) <span class="text-xs text-gray-400">({{ $r['telat_menit'] }}m)</span> @endif
                        </td>
                        <td class="py-3 px-3 text-center {{ $r['istirahat_lebih_hari'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $r['istirahat_lebih_hari'] }}x
                            @if($r['istirahat_lebih_menit'] > 0) <span class="text-xs text-gray-400">({{ $r['istirahat_lebih_menit'] }}m)</span> @endif
                        </td>
                        <td class="py-3 px-3 text-center {{ $r['pulang_awal_hari'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $r['pulang_awal_hari'] }}x
                            @if($r['pulang_awal_menit'] > 0) <span class="text-xs text-gray-400">({{ $r['pulang_awal_menit'] }}m)</span> @endif
                        </td>
                        <td class="py-3 px-3 text-center text-indigo-600 dark:text-indigo-400">{{ $r['cuti'] }}</td>
                        <td class="py-3 px-3 text-center {{ $r['alpha'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $r['alpha'] }}</td>
                        <td class="py-3 px-3 text-center text-purple-600 dark:text-purple-400">{{ $r['sakit'] }}</td>
                        <td class="py-3 px-3 text-center font-semibold {{ $r['persentase'] !== null ? ($r['persentase'] >= 90 ? 'text-emerald-600' : ($r['persentase'] >= 75 ? 'text-orange-600' : 'text-red-600')) : 'text-gray-400' }}">
                            {{ $r['persentase'] !== null ? $r['persentase'] . '%' : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data absensi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
@media print {
    .no-print { display: none !important; }
}
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('employeeSearch', () => ({
            query: '',
            selectedId: '{{ request("employee_id") }}',
            open: false,
            get filtered() {
                if (!this.query) return [];
                return employeeData.filter(e => e.name.toLowerCase().includes(this.query.toLowerCase()));
            },
            select(emp) {
                document.getElementById('search-employee-id').value = emp.id;
                this.selectedId = emp.id;
                this.query = emp.name;
                this.open = false;
                document.getElementById('report-form').submit();
            },
            init() {
                if (this.selectedId) {
                    const emp = employeeData.find(e => e.id == this.selectedId);
                    if (emp) this.query = emp.name;
                }
            }
        }));
    });
</script>
@endpush
