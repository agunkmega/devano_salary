@extends('layouts.admin')

@section('page-title', 'Saldo DP')
@section('page-subtitle', 'Input manual hari pengganti (DP) per pegawai oleh HR')

@section('page-content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 lg:col-span-1">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Tambah DP Pegawai</h3>
            <form action="{{ route('admin.dp.store') }}" method="POST" class="space-y-4">
                @csrf
                <div x-data="employeePicker()" @click.outside="open = false">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pegawai <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" x-model="query" @focus="open = true" @input="selected = null; open = true" placeholder="Ketik nama pegawai..."
                            class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                        <input type="hidden" name="employee_id" :value="selected ? selected.id : ''">
                        <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                            <template x-for="emp in filteredEmployees()" :key="emp.id">
                                <button type="button" @click="pick(emp)" class="w-full flex items-center justify-between gap-2 px-3 py-2.5 text-left text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                    <span class="text-gray-900 dark:text-white truncate" x-text="emp.name"></span>
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full flex-shrink-0" :class="emp.remaining > 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'" x-text="'sisa ' + emp.remaining"></span>
                                </button>
                            </template>
                            <div x-show="filteredEmployees().length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">Pegawai tidak ditemukan</div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Jumlah Hari <span class="text-red-500">*</span></label>
                        <input type="number" name="days" min="1" max="365" required value="1" class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="earned_date" required value="{{ old('earned_date', now()->format('Y-m-d')) }}" class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Keterangan</label>
                    <input type="text" name="note" maxlength="255" placeholder="cth: kerja saat libur nasional" class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Simpan</button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden lg:col-span-2">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Saldo DP Pegawai</h3>
                <form method="GET" class="flex items-center gap-2">
                    <input type="text" name="employee" value="{{ request('employee') }}" placeholder="Cari pegawai..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto overflow-y-auto" style="max-height:420px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                            <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Diberikan</th>
                            <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Terpakai</th>
                            <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Sisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4 text-gray-900 dark:text-white">{{ $emp['name'] }}</td>
                            <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-300">{{ $emp['granted'] }}</td>
                            <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-300">{{ $emp['used'] }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $emp['remaining'] > 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">{{ $emp['remaining'] }} hari</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pegawai aktif</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Pemberian DP</h3>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height:420px">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Hari</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Keterangan</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Diberikan Oleh</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grants as $g)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-4 text-gray-900 dark:text-white font-medium">{{ $g->earned_date->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-gray-900 dark:text-white">{{ $g->employee?->full_name ?? '-' }}</td>
                        <td class="py-3 px-4 text-center text-gray-900 dark:text-white">{{ $g->days }}</td>
                        <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ $g->note ?? '-' }}</td>
                        <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ $g->granter?->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-right">
                            <form action="{{ route('admin.dp.destroy', $g->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pemberian DP ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat pemberian DP</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $grants->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('employeePicker', () => ({
            employees: @json($employees),
            query: '',
            open: false,
            selected: null,
            filteredEmployees() {
                const q = this.query.trim().toLowerCase();
                if (!q) return this.employees;
                return this.employees.filter(e => e.name.toLowerCase().includes(q));
            },
            pick(emp) {
                this.selected = emp;
                this.query = emp.name;
                this.open = false;
            }
        }));
    });
</script>
@endpush
