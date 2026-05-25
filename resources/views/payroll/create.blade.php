@extends('layouts.admin')

@section('page-title', 'Buat Payroll')
@section('page-subtitle', 'Generate penggajian karyawan')

@section('page-content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('admin.payrolls.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pegawai <span class="text-red-500">*</span></label>
                    <div x-data="employeeSearch()" class="relative">
                        <input type="text" x-model="search" @focus="open = true" @input="open = true" @click.outside="open = false" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('employee_id') border-red-500 @enderror" placeholder="Ketik nama pegawai...">
                        <input type="hidden" name="employee_id" x-model="selected">
                        <ul x-show="open && filtered.length > 0" x-cloak class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="emp in filtered" :key="emp.id">
                                <li @click="select(emp)" class="px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer transition-colors" x-text="emp.full_name + ' (' + emp.nik + ')'"></li>
                            </template>
                        </ul>
                        <p x-show="selected && search" class="mt-1 text-xs text-emerald-600 dark:text-emerald-400" x-text="'Terpilih: ' + selectedName"></p>
                        <p x-show="selected && search" id="employee_type_info" class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="selectedType === 'harian' ? 'Jenis: Harian (dibayar per hari hadir)' : 'Jenis: Bulanan (gaji tetap per bulan)'"></p>
                    </div>
                    @error('employee_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Periode <span class="text-red-500">*</span></label>
                    <input type="month" name="period" value="{{ old('period', $period) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('period') border-red-500 @enderror" required>
                    @error('period') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bonus</label>
                    <input type="number" name="bonus" value="{{ old('bonus', 0) }}" min="0" step="0.01" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    @error('bonus') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Opsional: tambahan bonus di luar gaji pokok dan tunjangan</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="Catatan opsional">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.payrolls.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Generate Payroll</button>
            </div>
        </form>
    </div>

    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-4">
        <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Informasi Perhitungan</h4>
        <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-1" id="calc_info">
            <li id="calc_type_line">• <strong id="calc_type_label">Bulanan</strong>: <span id="calc_type_desc">Gaji pokok = gaji bulanan tetap</span></li>
            <li id="calc_overtime_line">• Lembur: <span id="calc_overtime_desc">1.5x gaji per jam (gaji/173)</span></li>
            <li>• Tunjangan diambil dari data master pegawai</li>
            <li>• Potongan keterlambatan: Rp {{ number_format((float) (\App\Models\Setting::where('key', 'late_penalty_per_minute')->value('value') ?: 2000), 0, ',', '.') }}/menit</li>
            <li>• Potongan BPJS: 2% dari gaji pokok</li>
            <li>• PPh 5% jika gaji bersih &gt; Rp 4.500.000</li>
            <li>• Potongan kasbon aktif otomatis jika ada</li>
        </ul>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('employeeSearch', () => ({
            search: '',
            open: false,
            selected: '{{ old('employee_id') }}',
            selectedName: '',
            selectedType: 'bulanan',
            employees: @json($employees->map(fn($e) => ['id' => $e->id, 'full_name' => $e->full_name, 'nik' => $e->nik, 'employee_type' => $e->employee_type])),
            init() {
                if (this.selected) {
                    const emp = this.employees.find(e => e.id == this.selected);
                    if (emp) { this.selectedName = emp.full_name; this.selectedType = emp.employee_type; this.search = emp.full_name; }
                }
                this.$watch('selected', (val) => {
                    const emp = this.employees.find(e => e.id == val);
                    this.selectedType = emp ? emp.employee_type : 'bulanan';
                });
                this.$watch('selectedType', (type) => {
                    const label = document.getElementById('calc_type_label');
                    const desc = document.getElementById('calc_type_desc');
                    const overtimeDesc = document.getElementById('calc_overtime_desc');
                    if (type === 'harian') {
                        if (label) label.textContent = 'Harian';
                        if (desc) desc.textContent = 'Gaji pokok = rate harian × jumlah hari hadir';
                        if (overtimeDesc) overtimeDesc.textContent = 'menggunakan rate lembur per jam dari data pegawai';
                    } else {
                        if (label) label.textContent = 'Bulanan';
                        if (desc) desc.textContent = 'Gaji pokok = gaji bulanan tetap';
                        if (overtimeDesc) overtimeDesc.textContent = '1.5x gaji per jam (gaji/173)';
                    }
                });
                this.$nextTick(() => { this.selectedType = this.selectedType; });
            },
            get filtered() {
                if (!this.search) return this.employees;
                const q = this.search.toLowerCase();
                return this.employees.filter(e => e.full_name.toLowerCase().includes(q));
            },
            select(emp) {
                this.selected = emp.id;
                this.selectedName = emp.full_name;
                this.selectedType = emp.employee_type;
                this.search = emp.full_name;
                this.open = false;
            },
        }));
    });
</script>
@endpush
@endsection