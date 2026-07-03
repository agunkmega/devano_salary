@extends('layouts.admin')

@section('page-title', 'Ajukan Kasbon')
@section('page-subtitle', 'Form pengajuan kasbon/pinjaman')

@section('page-content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.cash-advances.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Form Pengajuan Kasbon</h3>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Pengajuan</label>
                <input type="date" name="submission_date" value="{{ old('submission_date', date('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
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
                </div>
                @error('employee_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border border-blue-200 dark:border-blue-800 rounded-2xl p-5 bg-blue-50/50 dark:bg-blue-900/10">
                    <h4 class="text-base font-semibold text-blue-700 dark:text-blue-400 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Tunai
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah Pinjaman</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="number" name="tunai_amount" value="{{ old('tunai_amount') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('tunai_amount') border-red-500 @enderror" placeholder="0">
                            </div>
                            @error('tunai_amount') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah Cicilan</label>
                            <select name="tunai_installment_count" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 @error('tunai_installment_count') border-red-500 @enderror">
                                <option value="">Pilih Cicilan</option>
                                <option value="1" {{ old('tunai_installment_count') == '1' ? 'selected' : '' }}>1x</option>
                                <option value="3" {{ old('tunai_installment_count') == '3' ? 'selected' : '' }}>3x</option>
                                <option value="6" {{ old('tunai_installment_count') == '6' ? 'selected' : '' }}>6x</option>
                                <option value="12" {{ old('tunai_installment_count') == '12' ? 'selected' : '' }}>12x</option>
                            </select>
                            @error('tunai_installment_count') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Keterangan</label>
                            <textarea name="tunai_purpose" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('tunai_purpose') border-red-500 @enderror" placeholder="Alasan pengajuan kasbon tunai">{{ old('tunai_purpose') }}</textarea>
                            @error('tunai_purpose') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="border border-purple-200 dark:border-purple-800 rounded-2xl p-5 bg-purple-50/50 dark:bg-purple-900/10">
                    <h4 class="text-base font-semibold text-purple-700 dark:text-purple-400 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Non Tunai
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah Pinjaman</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="number" name="nontunai_amount" value="{{ old('nontunai_amount') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('nontunai_amount') border-red-500 @enderror" placeholder="0">
                            </div>
                            @error('nontunai_amount') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah Cicilan</label>
                            <select name="nontunai_installment_count" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 @error('nontunai_installment_count') border-red-500 @enderror">
                                <option value="">Pilih Cicilan</option>
                                <option value="1" {{ old('nontunai_installment_count') == '1' ? 'selected' : '' }}>1x</option>
                                <option value="3" {{ old('nontunai_installment_count') == '3' ? 'selected' : '' }}>3x</option>
                                <option value="6" {{ old('nontunai_installment_count') == '6' ? 'selected' : '' }}>6x</option>
                                <option value="12" {{ old('nontunai_installment_count') == '12' ? 'selected' : '' }}>12x</option>
                            </select>
                            @error('nontunai_installment_count') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Keterangan</label>
                            <textarea name="nontunai_purpose" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('nontunai_purpose') border-red-500 @enderror" placeholder="Alasan pengajuan kasbon non tunai">{{ old('nontunai_purpose') }}</textarea>
                            @error('nontunai_purpose') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            @error('at_least_one')
                <p class="mt-4 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.cash-advances.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Ajukan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('employeeSearch', () => ({
            search: '',
            open: false,
            selected: '{{ old('employee_id') }}',
            selectedName: '',
            employees: @json($employees->map(fn($e) => ['id' => $e->id, 'full_name' => $e->full_name])),
            init() {
                if (this.selected) {
                    const emp = this.employees.find(e => e.id == this.selected);
                    if (emp) { this.selectedName = emp.full_name; this.search = emp.full_name; }
                }
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
            },
        }));
    });
</script>
@endpush