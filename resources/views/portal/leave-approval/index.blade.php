@extends('portal.layouts.app')

@section('content')
<div x-data="rejectModal()" class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Persetujuan Cuti</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Departemen: {{ $department->name }}</p>
        </div>
    </div>

    @if($leaves->isEmpty())
    <div class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-gray-500 dark:text-gray-400">Tidak ada pengajuan cuti yang menunggu persetujuan.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($leaves as $leave)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $leave->employee?->full_name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $leave->leaveType?->name ?? 'Cuti' }} &middot; {{ $leave->total_days }} hari</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300">Pending</span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                <p><span class="text-gray-400">Tanggal:</span> {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</p>
                @if($leave->reason)
                <p><span class="text-gray-400">Alasan:</span> {{ $leave->reason }}</p>
                @endif
            </div>
            @if($leave->attachment)
            <div class="mt-2">
                <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Lihat Lampiran</a>
            </div>
            @endif
            <div class="flex gap-2 mt-4">
                <form action="{{ route('portal.leave-approval.approve', $leave) }}" method="POST" class="flex-1">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors">Setujui</button>
                </form>
                <button type="button" @click="openRejectModal({{ $leave->id }})" class="flex-1 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors">Tolak</button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

<div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="rejectModalOpen = false">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="rejectModalOpen = false"></div>
    <div x-show="rejectModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-sm w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Alasan Penolakan</h3>
        <form method="POST" x-bind:action="`${rejectBase}/${rejectLeaveId}/reject`">
            @csrf @method('PATCH')
            <textarea name="reason" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="Tulis alasan penolakan..." required></textarea>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" @click="rejectModalOpen = false; rejectLeaveId = null" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700">Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('rejectModal', () => ({
            rejectModalOpen: false,
            rejectLeaveId: null,
            rejectBase: '{{ url("portal/leave-approvals") }}',
            openRejectModal(id) { this.rejectLeaveId = id; this.rejectModalOpen = true; },
        }));
    });
</script>
@endsection
