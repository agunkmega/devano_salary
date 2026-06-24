@extends('layouts.admin')

@section('page-title', 'Laporan Payroll')
@section('page-subtitle', 'Rekap penggajian karyawan')

@section('page-content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <script>const employeeData = @js($employees->map(fn($e) => ['id' => $e->id, 'name' => $e->full_name])->values());</script>
        <form method="GET" action="{{ route('admin.reports.payroll') }}" class="flex flex-wrap gap-4 items-end" id="report-form">
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
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Station</label>
                <select name="station_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($stations as $station)
                    <option value="{{ $station->id }}" {{ request('station_id') == $station->id ? 'selected' : '' }}>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Bank</label>
                <select name="bank_name" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($banks as $bank)
                    <option value="{{ $bank }}" {{ request('bank_name') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
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
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Dibayar</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis</label>
                <select name="employee_type" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="bulanan" {{ request('employee_type') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="harian" {{ request('employee_type') == 'harian' ? 'selected' : '' }}>Harian</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Generate</button>
        </form>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['count'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Pegawai</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($summary['total_base_salary'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Gaji Pokok</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($summary['total_allowance'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Tunjangan</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($summary['total_overtime'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Lembur</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">Rp {{ number_format($summary['total_deductions'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Potongan</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($summary['total_net_salary'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Gaji Bersih</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">Rp {{ number_format($summary['total_bpjs_kesehatan'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">BPJS Kes. (Karyawan)</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-pink-600 dark:text-pink-400">Rp {{ number_format($summary['total_bpjs_kesehatan_company'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">BPJS Kes. (Perusahaan)</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($summary['total_bpjs_ketenagakerjaan'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">BPJS Ket. (Karyawan)</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">Rp {{ number_format($summary['total_bpjs_ketenagakerjaan_company'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">BPJS Ket. (Perusahaan)</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">Rp {{ number_format($summary['total_iuran_bulanan'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Iuran Bulanan</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">PPh 21</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">Rp {{ number_format($summary['total_cash_advance'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Kasbon</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Gaji</h3>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.payroll-print', request()->only(['period', 'department_id', 'employee_id', 'status', 'employee_type', 'station_id', 'bank_name'])) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </a>
                <a href="{{ route('admin.reports.payroll-print-detail', request()->only(['period', 'department_id', 'employee_id', 'status', 'employee_type', 'station_id', 'bank_name'])) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Detail
                </a>
                <a href="{{ route('admin.reports.payroll-excel-detail', request()->only(['period', 'department_id', 'employee_id', 'status', 'employee_type', 'station_id', 'bank_name'])) }}" class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel Detail
                </a>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm" style="min-width:1800px;white-space:nowrap">
                    <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                            <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Jabatan</th>
                            <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Jenis</th>
                            <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Bank</th>
                            <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">No. Rek</th>
                            <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Nama Rek</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Gaji Pokok</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Tunjangan</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Lembur</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Uang Makan</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Potongan</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium text-xs">BPJS Kes. (Kr)</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium text-xs">BPJS Kes. (Pr)</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium text-xs">BPJS Ket. (Kr)</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium text-xs">BPJS Ket. (Pr)</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium text-xs">Iuran Bul.</th>
                            <th class="text-right py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Gaji Bersih</th>
                            <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $p)
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-3 text-gray-900 dark:text-white font-medium">{{ $p->employee->full_name ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400">{{ $p->employee->position->name ?? $p->employee->department->name ?? '-' }}</td>
                            <td class="py-3 px-3 text-center">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ ($p->employee->employee_type ?? 'bulanan') === 'harian' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">{{ ($p->employee->employee_type ?? 'bulanan') === 'harian' ? 'Harian' : 'Bulanan' }}</span>
                            </td>
                            <td class="py-3 px-3 text-gray-900 dark:text-white">{{ $p->employee->bank_name ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-900 dark:text-white font-mono text-xs">{{ $p->employee->bank_account ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-900 dark:text-white">{{ $p->employee->bank_holder ?? '-' }}</td>
                            <td class="py-3 px-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($p->base_salary, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($p->allowance, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-indigo-600 dark:text-indigo-400">Rp {{ number_format($p->overtime_pay, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($p->uang_makan_lembur + $p->uang_makan_harian, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-red-600 dark:text-red-400">Rp {{ number_format($p->total_deductions, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-purple-600 dark:text-purple-400">Rp {{ number_format($p->bpjs_kesehatan_deduction, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-pink-600 dark:text-pink-400">Rp {{ number_format($p->bpjs_kesehatan_company, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-blue-600 dark:text-blue-400">Rp {{ number_format($p->bpjs_ketenagakerjaan_deduction, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-cyan-600 dark:text-cyan-400">Rp {{ number_format($p->bpjs_ketenagakerjaan_company, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-orange-600 dark:text-orange-400">Rp {{ number_format($p->iuran_bulanan_deduction, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-center">
                                @if($p->status == 'paid')
                                <span class="px-2 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg">Dibayar</span>
                                @elseif($p->status == 'approved')
                                <span class="px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/50 rounded-lg">Disetujui</span>
                                @elseif($p->status == 'cancelled')
                                <span class="px-2 py-1 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/50 rounded-lg">Dibatalkan</span>
                                @else
                                <span class="px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Draft</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="18" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data payroll</td></tr>
                        @endforelse
                    </tbody>
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
