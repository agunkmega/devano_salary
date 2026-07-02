@extends('layouts.admin')

@section('page-title', 'Laporan BPJS')
@section('page-subtitle', 'Rekap BPJS Kesehatan & Ketenagakerjaan')

@section('page-content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <script>const employeeData = @js($employees->map(fn($e) => ['id' => $e->id, 'name' => $e->full_name])->values());</script>
        <form method="GET" action="{{ route('admin.reports.bpjs') }}" class="flex flex-wrap gap-4 items-end" id="report-form">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Periode</label>
                <select name="period" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($periods as $p)
                    <option value="{{ $p }}" {{ request('period') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
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
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis BPJS</label>
                <select name="bpjs_type" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="kesehatan" {{ request('bpjs_type') == 'kesehatan' ? 'selected' : '' }}>Kesehatan</option>
                    <option value="ketenagakerjaan" {{ request('bpjs_type') == 'ketenagakerjaan' ? 'selected' : '' }}>Ketenagakerjaan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Station</label>
                <select name="station_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($stations as $s)
                    <option value="{{ $s->id }}" {{ request('station_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tipe BPJS Ket.</label>
                <select name="bpjs_ket_type" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="full" {{ request('bpjs_ket_type') == 'full' ? 'selected' : '' }}>Full</option>
                    <option value="partial" {{ request('bpjs_ket_type') == 'partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Generate</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail BPJS</h3>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.bpjs-print', request()->all()) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </a>
                <a href="{{ route('admin.reports.bpjs-pdf', request()->all()) }}" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF
                </a>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">NIK</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Dept</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Jenis BPJS</th>
                        <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Perusahaan</th>
                        <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Karyawan</th>
                        <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotalPerusahaan = 0; $grandTotalKaryawan = 0; $grandTotal = 0; $bpjsType = request('bpjs_type'); @endphp
                    @forelse($payrolls as $p)
                        @php
                            $hasKes = ($p->bpjs_kesehatan_deduction > 0 || $p->bpjs_kesehatan_company > 0);
                            $hasKet = ($p->bpjs_ketenagakerjaan_deduction > 0 || $p->bpjs_ketenagakerjaan_company > 0);
                            $showKes = $hasKes && (!$bpjsType || $bpjsType === 'kesehatan');
                            $showKet = $hasKet && (!$bpjsType || $bpjsType === 'ketenagakerjaan');
                        @endphp
                        @if($showKes)
                        @php
                            $totalKes = $p->bpjs_kesehatan_company + $p->bpjs_kesehatan_deduction;
                            $grandTotalPerusahaan += $p->bpjs_kesehatan_company;
                            $grandTotalKaryawan += $p->bpjs_kesehatan_deduction;
                            $grandTotal += $totalKes;
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-3 text-gray-900 dark:text-white font-mono text-xs">{{ $p->identity_number ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-900 dark:text-white font-medium">{{ $p->full_name }}</td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400">{{ $p->employee->department->name ?? '-' }}</td>
                            <td class="py-3 px-3"><span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">Kesehatan</span></td>
                            <td class="py-3 px-3 text-right text-pink-600 dark:text-pink-400">Rp {{ number_format($p->bpjs_kesehatan_company, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-purple-600 dark:text-purple-400">Rp {{ number_format($p->bpjs_kesehatan_deduction, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($totalKes, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($showKet)
                        @php
                            $totalKet = $p->bpjs_ketenagakerjaan_company + $p->bpjs_ketenagakerjaan_deduction;
                            $grandTotalPerusahaan += $p->bpjs_ketenagakerjaan_company;
                            $grandTotalKaryawan += $p->bpjs_ketenagakerjaan_deduction;
                            $grandTotal += $totalKet;
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-3 text-gray-900 dark:text-white font-mono text-xs">{{ $p->identity_number ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-900 dark:text-white font-medium">{{ $p->full_name }}</td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400">{{ $p->employee->department->name ?? '-' }}</td>
                            <td class="py-3 px-3"><span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">Ketenagakerjaan</span>@if(($p->employee->bpjs_ketenagakerjaan_type ?? 'full') === 'partial') <span class="text-[10px] text-gray-400 ml-1">(partial)</span>@endif</td>
                            <td class="py-3 px-3 text-right text-cyan-600 dark:text-cyan-400">Rp {{ number_format($p->bpjs_ketenagakerjaan_company, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-blue-600 dark:text-blue-400">Rp {{ number_format($p->bpjs_ketenagakerjaan_deduction, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($totalKet, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @empty
                    <tr><td colspan="7" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data BPJS</td></tr>
                    @endforelse
                </tbody>
                @if($payrolls->total() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-300 dark:border-gray-600 font-semibold">
                        <td colspan="4" class="py-3 px-3 text-right text-gray-900 dark:text-white">Grand Total</td>
                        <td class="py-3 px-3 text-right text-pink-600 dark:text-pink-400">Rp {{ number_format($grandTotalPerusahaan, 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right text-purple-600 dark:text-purple-400">Rp {{ number_format($grandTotalKaryawan, 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right font-bold text-gray-900 dark:text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($payrolls->hasPages())
        <div class="mt-4">
            {{ $payrolls->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
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
