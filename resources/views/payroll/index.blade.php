@extends('layouts.admin')

@section('page-title', 'Payroll')
@section('page-subtitle', 'Kelola penggajian karyawan')

@section('page-content')
<div class="space-y-6">
    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.payrolls.index') }}" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-wrap gap-3 flex-1">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Periode</label>
                <select name="period" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Periode</option>
                    @foreach($periodsData as $pd)
                        <option value="{{ $pd['value'] }}" {{ request('period') == $pd['value'] ? 'selected' : '' }}>{{ $pd['range'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Departemen</label>
                <select name="department_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Station</label>
                <select name="station_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}" {{ request('station_id') == $station->id ? 'selected' : '' }}>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>
            <div x-data="filterEmployeeSearch()">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pegawai</label>
                <div class="relative">
                    <input type="text" x-model="search" @focus="open = true" @input="open = true" @click.outside="open = false" class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Ketik nama...">
                    <input type="hidden" name="employee_id" x-model="selected">
                    <ul x-show="open && filtered.length > 0" x-cloak class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                        <li @click="clear()" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition-colors">Semua</li>
                        <template x-for="emp in filtered" :key="emp.id">
                            <li @click="select(emp)" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer transition-colors" x-text="emp.full_name"></li>
                        </template>
                    </ul>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis</label>
                <select name="employee_type" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="bulanan" {{ request('employee_type') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="harian" {{ request('employee_type') == 'harian' ? 'selected' : '' }}>Harian</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Filter</button>
            </div>
        </div>
    </form>

    <div class="flex items-center gap-2">
        <a href="{{ route('admin.payrolls.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Payroll Manual
        </a>
        <form action="{{ route('admin.payrolls.generate-all') }}" method="POST" class="inline" x-init="() => { document.getElementById('generate_date_from').value = document.querySelector('input[name=date_from]').value; document.getElementById('generate_date_to').value = document.querySelector('input[name=date_to]').value; }">
            @csrf
            <input type="hidden" name="date_from" id="generate_date_from" value="{{ $dateFrom }}">
            <input type="hidden" name="date_to" id="generate_date_to" value="{{ $dateTo }}">
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open; document.getElementById('generate_date_from').value = document.querySelector('input[name=date_from]').value; document.getElementById('generate_date_to').value = document.querySelector('input[name=date_to]').value" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Generate
                </button>
                <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50" style="display: none;">
                    <div class="p-4 space-y-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Periode: <span id="generate_period_label">{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</span></p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pegawai</label>
                            <div x-data="employeeSearch()" class="relative">
                                <input type="text" x-model="search" @focus="open = true" @input="open = true" @click.outside="open = false" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="Ketik nama...">
                                <input type="hidden" name="employee_id" x-model="selected">
                                <ul x-show="open && filtered.length > 0" x-cloak class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="emp in filtered" :key="emp.id">
                                        <li @click="select(emp)" class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer transition-colors" x-text="emp.full_name"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        <button type="submit" @click="window.startGenerate($event); document.getElementById('generate_date_from').value = document.querySelector('input[name=date_from]').value; document.getElementById('generate_date_to').value = document.querySelector('input[name=date_to]').value" class="w-full px-3 py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700">Generate</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tipe</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Periode</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Gaji Pokok</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tunjangan</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Lembur</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Uang Makan</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pot. Telat</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pot. Absen</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Kasbon</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pot. Wajib</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Gaji Bersih</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $p)
                    @php
                        $name = $p->employee?->full_name ?? 'Unknown';
                        $empType = $p->employee?->employee_type ?? 'bulanan';
                        $words = preg_split('/\s+/', trim($name));
                        $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? $words[0] ?? '', 0, 1));
                        [$year, $month] = explode('-', $p->period);
                        $monthNames = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                        $periodStart = \Carbon\Carbon::create((int)$year, (int)$month, 26)->subMonth();
                        $periodEnd = \Carbon\Carbon::create((int)$year, (int)$month, 25);
                        $periodRange = $periodStart->format('d M') . ' - ' . $periodEnd->format('d M Y');
                        $statusMap = ['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Disetujui', 'paid' => 'Dibayar'];
                        $fmt = function($v) { return 'Rp ' . number_format((float) $v, 0, ',', '.'); };
                    @endphp
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-xs font-semibold text-blue-600 dark:text-blue-400">{{ $initials }}</div>
                                <span class="text-gray-900 dark:text-white font-medium">{{ $name }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $empType === 'harian' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">{{ $empType === 'harian' ? 'Harian' : 'Bulanan' }}</span>
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $periodRange }}</td>
                        <td class="py-3 px-4 text-right text-gray-900 dark:text-white">
                            @if($empType === 'harian' && $p->attendance_days !== null)
                                {{ $fmt($p->base_salary) }}
                                <span class="text-xs text-gray-500 dark:text-gray-400 block">({{ $p->attendance_days }} hari)</span>
                            @else
                                {{ $fmt($p->base_salary) }}
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right text-gray-900 dark:text-white">{{ $fmt($p->allowance) }}</td>
                        <td class="py-3 px-4 text-right text-gray-900 dark:text-white">{{ $fmt($p->overtime_pay) }}</td>
                        <td class="py-3 px-4 text-right text-gray-900 dark:text-white">{{ $fmt($p->uang_makan_lembur) }}</td>
                        <td class="py-3 px-4 text-right text-red-600 dark:text-red-400">{{ $fmt($p->late_penalty + $p->late_penalty_percent) }}</td>
                        <td class="py-3 px-4 text-right text-red-600 dark:text-red-400">{{ $fmt($p->absent_penalty) }}</td>
                        <td class="py-3 px-4 text-right text-red-600 dark:text-red-400">{{ $fmt($p->cash_advance_deduction) }}</td>
                        <td class="py-3 px-4 text-right text-red-600 dark:text-red-400">{{ $fmt($p->bpjs_deduction + $p->tax_amount) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">{{ $fmt($p->net_salary) }}</td>
                        <td class="py-3 px-4">
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full @switch($p->status)
                                @case('draft') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 @break
                                @case('pending') bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 @break
                                @case('approved') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 @break
                                @case('paid') bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 @break
                                @default bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400
                            @endswitch">{{ $statusMap[$p->status] ?? ucfirst($p->status) }}</span>
                        </td>
                        <td class="py-3 px-2 text-right">
                            <div class="flex items-center justify-end gap-0">
                                <a href="{{ route('admin.payrolls.show', $p->id) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Detail">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.payrolls.edit', $p->id) }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if($p->status === 'draft' || $p->status === 'pending')
                                <form action="{{ route('admin.payrolls.approve', $p->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors" title="Setujui">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                                @endif
                                @if($p->status === 'approved')
                                <form action="{{ route('admin.payrolls.pay', $p->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Bayar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.payrolls.regenerate', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Regenerate payroll ini? Data lama akan dihapus dan dihitung ulang.')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors" title="Regenerate">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route('admin.payrolls.destroy', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus payroll ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="py-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada data payroll</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ $payrolls->firstItem() }} - {{ $payrolls->lastItem() }} dari {{ $payrolls->total() }}
            </p>
            {{ $payrolls->withQueryString()->links() }}
        </div>
    </div>
</div>




@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('employeeSearch', () => ({
            search: '',
            open: false,
            selected: '',
            selectedName: '',
            employees: @json($employees->map(fn($e) => ['id' => $e->id, 'full_name' => $e->full_name])),
            get filtered() {
                if (!this.search) return this.employees;
                const q = this.search.toLowerCase();
                return this.employees.filter(e => e.full_name.toLowerCase().includes(q));
            },
            select(emp) {
                this.selected = emp.id;
                this.selectedName = emp.full_name;
                this.search = emp.full_name;
                this.open = false;
            },
        }));
        Alpine.data('filterEmployeeSearch', () => ({
            search: '{{ request('employee_id') ? ($employees->firstWhere('id', request('employee_id'))?->full_name ?? '') : '' }}',
            open: false,
            selected: '{{ request('employee_id') }}',
            employees: @json($employees->map(fn($e) => ['id' => $e->id, 'full_name' => $e->full_name])),
            get filtered() {
                if (!this.search) return this.employees;
                const q = this.search.toLowerCase();
                return this.employees.filter(e => e.full_name.toLowerCase().includes(q));
            },
            select(emp) {
                this.selected = emp.id;
                this.search = emp.full_name;
                this.open = false;
            },
            clear() {
                this.selected = '';
                this.search = '';
                this.open = false;
            },
        }));
        Alpine.data('generateProgress', () => ({
            show: false,
            current: 0,
            total: 0,
            status: 'idle',
            polling: null,
            start(e) {
                this.show = true;
                this.current = 0;
                this.total = 0;
                this.status = 'processing';
                const form = e.target.closest('form');
                if (!form) return;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json()).then(d => {
                    this.status = 'complete';
                    setTimeout(() => window.location.reload(), 1200);
                }).catch(() => {
                    this.status = 'complete';
                    window.location.reload();
                });
                this.polling = setInterval(() => {
                    fetch('/admin/payrolls/generation-progress', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(p => {
                            this.current = p.current;
                            this.total = p.total;
                            if (p.status === 'complete') { clearInterval(this.polling); this.polling = null; }
                        }).catch(() => {});
                }, 1500);
            }
        }));
        window.startGenerate = function(e) {
            const el = document.querySelector('[x-data="generateProgress()"]');
            if (el && el.__x) el.__x.$data.start(e);
        };
    });
</script>
@endpush

<div x-data="generateProgress()">
    <template x-teleport="body">
        <div x-show="show" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center">
                <template x-if="status === 'complete'">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </template>
                <template x-if="status !== 'complete'">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                </template>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2" x-text="status === 'complete' ? 'Generate Selesai' : 'Generate Payroll'"></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" x-text="status === 'complete' ? 'Payroll berhasil digenerate.' : 'Mohon tunggu, sedang memproses payroll...'"></p>
                <template x-if="status !== 'complete' && total > 0">
                    <div class="space-y-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full transition-all duration-500 ease-out" :style="'width: ' + Math.round((current / total) * 100) + '%'"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="current + ' / ' + total + ' pegawai'"></p>
                    </div>
                </template>
                <template x-if="status !== 'complete' && total === 0">
                    <div class="space-y-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full animate-pulse" style="width: 30%"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Menyiapkan data...</p>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
@endsection