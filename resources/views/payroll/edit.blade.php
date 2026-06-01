@extends('layouts.admin')

@section('page-title', 'Edit Payroll')
@section('page-subtitle')
    @php
        $perStart = \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->subMonth()->day(26);
        $perEnd = \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->day(25);
    @endphp
    {{ $perStart->format('d M') }} - {{ $perEnd->format('d M Y') }} - {{ $payroll->employee?->full_name ?? 'Unknown' }}
@endsection

@section('page-content')
@php $fmt = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.payrolls.update', $payroll->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to" value="{{ request('date_to') }}">

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Edit Payroll</h3>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pegawai</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $payroll->employee?->full_name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Gaji Pokok</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->base_salary) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Tunjangan</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->allowance) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Lembur + Uang Makan</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($payroll->overtime_pay + $payroll->uang_makan_lembur) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Potongan</p>
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $fmt($payroll->total_deductions) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Gaji Bersih Saat Ini</p>
                        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $fmt($payroll->net_salary) }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Uang Makan Harian <span class="text-xs text-gray-400">(isi manual)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="number" name="uang_makan_harian" value="{{ old('uang_makan_harian', $payroll->uang_makan_harian ?? '0') }}" min="0" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('uang_makan_harian') border-red-500 @enderror" placeholder="0">
                    </div>
                    @error('uang_makan_harian') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bonus</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="number" name="bonus" value="{{ old('bonus', $payroll->bonus ?? '0') }}" min="0" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('bonus') border-red-500 @enderror" placeholder="0">
                    </div>
                    @error('bonus') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="Catatan payroll">{{ old('notes', $payroll->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.payrolls.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection