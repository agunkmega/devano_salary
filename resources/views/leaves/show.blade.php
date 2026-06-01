@extends('layouts.admin')

@section('page-title', 'Detail Cuti')
@section('page-subtitle', $leave->employee?->full_name ?? 'Unknown')

@section('page-content')
@php
    $statusMap = ['pending'=>'Pending','approved'=>'Disetujui','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'];
    $fmt = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xl font-bold">
                    {{ substr($leave->employee?->full_name ?? 'U', 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $leave->employee?->full_name ?? 'Unknown' }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $leave->employee?->nik ?? '-' }} | {{ $leave->employee?->department?->name ?? '-' }}</p>
                </div>
            </div>
            <span class="text-xs font-medium px-3 py-1.5 rounded-full @switch($leave->status)
                @case('pending') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 @break
                @case('approved') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 @break
                @case('rejected') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 @break
                @case('cancelled') bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 @break
                @default bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400
            @endswitch">{{ $statusMap[$leave->status] ?? ucfirst($leave->status) }}</span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Cuti</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $leave->leaveType?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Hari</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $leave->total_days }} hari</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Mulai</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $leave->start_date?->format('d M Y') ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Selesai</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $leave->end_date?->format('d M Y') ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $leave->reason ?? '-' }}</dd>
            </div>
            @if($leave->attachment)
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lampiran</dt>
                <dd class="mt-1">
                    <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Lihat Lampiran
                    </a>
                </dd>
            </div>
            @endif
            @if($leave->status === 'rejected' && $leave->notes)
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-red-500 uppercase tracking-wider">Alasan Ditolak</dt>
                <dd class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $leave->notes }}</dd>
            </div>
            @endif
            @if($leave->approver)
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Disetujui/Ditolak Oleh</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $leave->approver->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Approval</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $leave->approval_date?->format('d M Y H:i') ?? '-' }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <div class="flex justify-end gap-3">
        @if($leave->status === 'approved')
            <a href="{{ route('admin.leaves.edit', $leave->id) }}" class="px-6 py-2.5 text-sm font-medium text-amber-700 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 rounded-xl hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors">Edit</a>
        @endif
        <a href="{{ route('admin.leaves.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Kembali</a>
    </div>
</div>
@endsection