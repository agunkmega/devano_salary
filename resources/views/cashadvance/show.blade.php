@extends('layouts.admin')

@section('page-title', 'Detail Kasbon')
@section('page-subtitle', $cashAdvance->employee?->full_name ?? 'Unknown')

@section('page-content')
@php
    $statusMap = ['pending'=>'Pending','approved'=>'Disetujui','rejected'=>'Ditolak','paid'=>'Lunas'];
    $typeMap = ['tunai'=>'Tunai','nontunai'=>'Non Tunai'];
    $fmt = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl font-bold">
                    {{ substr($cashAdvance->employee?->full_name ?? 'U', 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $cashAdvance->employee?->full_name ?? 'Unknown' }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $cashAdvance->employee?->nik ?? '-' }}</p>
                </div>
            </div>
            <span class="text-xs font-medium px-3 py-1.5 rounded-full @switch($cashAdvance->status)
                @case('pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 @break
                @case('approved') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 @break
                @case('rejected') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 @break
                @case('paid') bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 @break
                @default bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400
            @endswitch">{{ $statusMap[$cashAdvance->status] ?? ucfirst($cashAdvance->status) }}</span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</dt>
                <dd class="mt-1 text-sm font-medium">
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $cashAdvance->type === 'nontunai' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">
                        {{ $typeMap[$cashAdvance->type] ?? 'Tunai' }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah Pinjaman</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($cashAdvance->amount) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cicilan</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $cashAdvance->installment_count }}x ({{ $fmt($cashAdvance->installment_amount) }}/bulan)</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sisa</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $fmt($cashAdvance->remaining_amount) }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $cashAdvance->purpose ?? '-' }}</dd>
            </div>
            @if($cashAdvance->status === 'rejected' && $cashAdvance->notes)
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-red-500 uppercase tracking-wider">Alasan Ditolak</dt>
                <dd class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $cashAdvance->notes }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.cash-advances.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Kembali</a>
    </div>
</div>
@endsection
