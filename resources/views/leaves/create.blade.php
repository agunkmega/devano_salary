@extends('layouts.admin')

@section('page-title', 'Ajukan Cuti')
@section('page-subtitle', 'Form pengajuan cuti atau izin')

@section('page-content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Form Pengajuan</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pegawai <span class="text-red-500">*</span></label>
                    <div x-data="employeeSearch()" class="relative">
                        <input type="text" x-model="search" @focus="open = true" @input="open = true" @click.outside="open = false" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('employee_id') border-red-500 @enderror" placeholder="Ketik nama pegawai...">
                        <input type="hidden" name="employee_id" x-model="selected">
                        <ul x-show="open && filtered.length > 0" x-cloak class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="emp in filtered" :key="emp.id">
                                <li @click="select(emp)" class="px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer transition-colors" x-text="emp.full_name"></li>
                            </template>
                        </ul>
                        <p x-show="selected && search" class="mt-1 text-xs text-emerald-600 dark:text-emerald-400" x-text="'Terpilih: ' + selectedName"></p>
                        <div x-show="balances" x-cloak class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-xs font-medium text-blue-700 dark:text-blue-300">
                                Sisa CT: <span class="font-bold ml-1" x-text="(balances ? balances.ct_remaining : 0) + ' hari'"></span>
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-xs font-medium text-purple-700 dark:text-purple-300">
                                Sisa DP: <span class="font-bold ml-1" x-text="(balances ? balances.dp_remaining : 0) + ' hari'"></span>
                            </span>
                            <span x-show="balances && !balances.cuti_eligible" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-xs font-medium text-gray-500 dark:text-gray-400">
                                Belum memenuhi syarat cuti tahunan
                            </span>
                        </div>
                    </div>
                    @error('employee_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis <span class="text-red-500">*</span></label>
                    <select name="leave_type_id" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 @error('leave_type_id') border-red-500 @enderror">
                        <option value="">Pilih Jenis</option>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                        @endforeach
                    </select>
                    @error('leave_type_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tgl Pengajuan <span class="text-red-500">*</span></label>
                        <input type="date" name="submission_date" value="{{ old('submission_date', date('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('submission_date') border-red-500 @enderror">
                        @error('submission_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" x-model="startDate" @change="calcDays" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" x-model="endDate" @change="calcDays" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-500 @enderror">
                        @error('end_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Total hari: <span class="font-bold" x-text="totalDays + ' hari'"></span></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Keterangan <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="4" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('reason') border-red-500 @enderror" placeholder="Alasan pengajuan cuti/izin">{{ old('reason') }}</textarea>
                    @error('reason') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Lampiran</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 dark:file:bg-blue-900/20 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PDF, JPG, PNG max 2MB</p>
                    @error('attachment') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.leaves.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Ajukan</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('leaveForm', () => ({
            leaveType: '',
            startDate: '',
            endDate: '',
            totalDays: 0,
            calcDays() {
                if (this.startDate && this.endDate) {
                    const start = new Date(this.startDate);
                    const end = new Date(this.endDate);
                    const diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    this.totalDays = diff > 0 ? diff : 0;
                }
            }
        }));
        Alpine.data('employeeSearch', () => ({
            search: '',
            open: false,
            selected: '{{ old('employee_id') }}',
            selectedName: '',
            balances: null,
            balanceUrl: @js(route('admin.leaves.balance', ['employee' => 'EMPID'])),
            employees: @json($employees->map(fn($e) => ['id' => $e->id, 'full_name' => $e->full_name])),
            init() {
                if (this.selected) {
                    const emp = this.employees.find(e => e.id == this.selected);
                    if (emp) { this.selectedName = emp.full_name; this.search = emp.full_name; }
                    this.fetchBalance();
                }
            },
            fetchBalance() {
                if (!this.selected) { this.balances = null; return; }
                fetch(this.balanceUrl.replace('EMPID', this.selected), {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(d => this.balances = d)
                    .catch(() => this.balances = null);
            },
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
                this.fetchBalance();
            },
        }));
    });
</script>
@endpush
@endsection
