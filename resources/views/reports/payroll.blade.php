@extends('layouts.admin')

@section('page-title', 'Laporan Payroll')
@section('page-subtitle', 'Rekap penggajian karyawan')

@section('page-content')
<div x-data="reportPayroll()" x-init="init()" class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Periode</label>
                <select x-model="period" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option>Januari 2024</option>
                    <option>Februari 2024</option>
                    <option>Maret 2024</option>
                    <option>April 2024</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Departemen</label>
                <select x-model="filters.department" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Departemen</option>
                    <option>IT</option>
                    <option>HRD</option>
                    <option>Finance</option>
                    <option>Marketing</option>
                </select>
            </div>
            <button @click="generate" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Generate</button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pegawai</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">64</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Gaji Pokok</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp 320.000.000</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Tunjangan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp 48.000.000</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Potongan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp 12.500.000</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Gaji Bersih</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp 355.500.000</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total BPJS</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp 9.600.000</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total PPh 21</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp 6.400.000</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Kasbon</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp 2.500.000</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Gaji per Departemen</h3>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Excel</button>
                <button onclick="window.print()" class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Print</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Departemen</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Gaji Pokok</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tunjangan</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Potongan</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Gaji Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="d in departmentData" :key="d.name">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="py-3 px-4 text-gray-900 dark:text-white font-medium" x-text="d.name"></td>
                            <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-400" x-text="d.count"></td>
                            <td class="py-3 px-4 text-right text-gray-900 dark:text-white" x-text="d.base"></td>
                            <td class="py-3 px-4 text-right text-gray-900 dark:text-white" x-text="d.allowance"></td>
                            <td class="py-3 px-4 text-right text-red-600 dark:text-red-400" x-text="d.deductions"></td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400" x-text="d.net"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportPayroll', () => ({
            period: 'Januari 2024',
            filters: { department: '' },
            departmentData: [
                { name: 'IT', count: 15, base: 'Rp 95.000.000', allowance: 'Rp 15.000.000', deductions: 'Rp 3.200.000', net: 'Rp 106.800.000' },
                { name: 'HRD', count: 8, base: 'Rp 42.000.000', allowance: 'Rp 6.000.000', deductions: 'Rp 1.800.000', net: 'Rp 46.200.000' },
                { name: 'Finance', count: 10, base: 'Rp 58.000.000', allowance: 'Rp 9.000.000', deductions: 'Rp 2.500.000', net: 'Rp 64.500.000' },
                { name: 'Marketing', count: 12, base: 'Rp 65.000.000', allowance: 'Rp 10.000.000', deductions: 'Rp 2.800.000', net: 'Rp 72.200.000' },
            ],
            generate() {},
            init() {}
        }));
    });
</script>
@endpush
@endsection
