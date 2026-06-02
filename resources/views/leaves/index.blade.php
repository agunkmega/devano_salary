@extends('layouts.admin')

@section('page-title', 'Cuti / Izin')
@section('page-subtitle', 'Kelola pengajuan cuti dan izin')

@section('page-content')
<div x-data="leaveList()" x-init="init()" class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.leaves.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pegawai</label>
                <input type="text" name="employee" value="{{ request('employee') }}" placeholder="Cari pegawai..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Cari</button>
        </form>
        <a href="{{ route('admin.leaves.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ajukan Cuti
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jenis</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Hari</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="leave in filteredLeaves" :key="leave.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-xs font-semibold text-blue-600 dark:text-blue-400" x-text="leave.initials"></div>
                                    <span class="text-gray-900 dark:text-white font-medium" x-text="leave.employee"></span>
                                </div>
                            </td>
                            <td class="py-3 px-4"><span class="text-gray-900 dark:text-white" x-text="leave.type"></span></td>
                            <td class="py-3 px-4"><span class="text-gray-600 dark:text-gray-400" x-text="leave.dates"></span></td>
                            <td class="py-3 px-4 text-center text-gray-900 dark:text-white font-medium" x-text="leave.days"></td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="{
                                    'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400': leave.status === 'Pending',
                                    'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400': leave.status === 'Disetujui',
                                    'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400': leave.status === 'Ditolak'
                                }" x-text="leave.status"></span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a :href="`/admin/leaves/${leave.id}`" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <template x-if="leave.status === 'Pending'">
                                        <div class="flex items-center gap-1">
                                            <button @click="openApprovalModal(leave, 'Disetujui')" class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg hover:bg-emerald-200 dark:hover:bg-emerald-900/50 transition-colors">Setujui</button>
                                            <button @click="openApprovalModal(leave, 'Ditolak')" class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">Tolak</button>
                                        </div>
                                    </template>
                                    <template x-if="leave.status === 'Disetujui'">
                                        <a :href="`/admin/leaves/${leave.id}/edit`" class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    </template>
                                    <template x-if="leave.status !== 'Pending' && leave.status !== 'Disetujui'">
                                        <span class="text-xs text-gray-400">-</span>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan {{ $leaves->firstItem() }} - {{ $leaves->lastItem() }} dari {{ $leaves->total() }}
        </p>
        {{ $leaves->withQueryString()->links() }}
    </div>

    <div x-show="approvalModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="approvalModal = false">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="approvalModal = false"></div>
        <div x-show="approvalModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2" x-text="approvalAction === 'Disetujui' ? 'Setujui Pengajuan Cuti' : 'Tolak Pengajuan Cuti'"></h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                <span x-text="approving?.employee"></span> - <span x-text="approving?.type"></span> (<span x-text="approving?.dates"></span>)
            </p>
            <form :action="approvalAction === 'Disetujui' ? `/admin/leaves/${approving?.id}/approve` : `/admin/leaves/${approving?.id}/reject`" method="POST">
                @csrf
                @method('PATCH')
                <div x-show="approvalAction === 'Ditolak'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Catatan <span class="text-red-500">*</span></label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="Alasan penolakan"></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="approvalModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white rounded-xl" :class="approvalAction === 'Disetujui' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'" x-text="approvalAction"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('leaveList', () => ({
            filters: { status: '', employee: '' },
            approvalModal: false,
            approving: null,
            approvalAction: '',
            leaves: @json($leavesData->items()),
            get filteredLeaves() {
                return this.leaves.filter(l => {
                    if (this.filters.status && l.status !== this.filters.status) return false;
                    if (this.filters.employee && !l.employee.toLowerCase().includes(this.filters.employee.toLowerCase())) return false;
                    return true;
                });
            },
            openApprovalModal(leave, action) { this.approving = leave; this.approvalAction = action; this.approvalModal = true; },
            init() {}
        }));
    });
</script>
@endpush
@endsection
