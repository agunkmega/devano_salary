@extends('layouts.admin')

@section('page-title', 'Kasbon')
@section('page-subtitle', 'Kelola pengajuan kasbon/pinjaman')

@section('page-content')
<div x-data="cashAdvanceList()" x-init="init()" class="space-y-6">
    <div class="flex justify-end">
        <a href="{{ route('admin.cash-advances.create') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ajukan Kasbon
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tgl. Pengajuan</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jenis</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jumlah</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Cicilan</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Sisa</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="ca in cashAdvances" :key="ca.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400 text-xs" x-text="ca.submission_date"></td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-xs font-semibold text-indigo-600 dark:text-indigo-400" x-text="ca.initials"></div>
                                    <span class="text-gray-900 dark:text-white font-medium" x-text="ca.employee"></span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="ca.type === 'Non Tunai' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'" x-text="ca.type"></span>
                            </td>
                            <td class="py-3 px-4 text-gray-900 dark:text-white font-medium" x-text="ca.amount"></td>
                            <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-400" x-text="ca.installments + 'x'"></td>
                            <td class="py-3 px-4 text-gray-900 dark:text-white font-medium" x-text="ca.remaining"></td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="{
                                    'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400': ca.status === 'Pending',
                                    'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400': ca.status === 'Disetujui',
                                    'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400': ca.status === 'Ditolak',
                                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400': ca.status === 'Lunas'
                                }" x-text="ca.status"></span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <template x-if="ca.status === 'Pending'">
                                        <div class="flex items-center gap-1">
                                            <form :action="`/admin/cash-advances/${ca.id}/approve`" method="POST" class="inline" @submit="$event.target.querySelector('button').disabled = true">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg hover:bg-emerald-200 transition-colors">Setujui</button>
                                            </form>
                                            <button @click="openRejectModal(ca)" class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-400 rounded-lg hover:bg-red-200 transition-colors">Tolak</button>
                                        </div>
                                    </template>
                                    <a :href="`/admin/cash-advances/${ca.id}/edit`" class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg hover:bg-blue-200 transition-colors">Edit</a>
                                    <form :action="`/admin/cash-advances/${ca.id}`" method="POST" class="inline" onsubmit="return confirm('Hapus kasbon ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-400 rounded-lg hover:bg-red-200 transition-colors">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="rejectModal = false">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="rejectModal = false"></div>
        <div x-show="rejectModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tolak Pengajuan Kasbon</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                <span x-text="rejecting?.employee"></span> - <span x-text="rejecting?.amount"></span>
            </p>
            <form :action="`/admin/cash-advances/${rejecting?.id}/reject`" method="POST">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Catatan <span class="text-red-500">*</span></label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="Alasan penolakan" required></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cashAdvanceList', () => ({
            cashAdvances: @json($cashAdvancesData->items()),
            rejectModal: false,
            rejecting: null,
            openRejectModal(ca) { this.rejecting = ca; this.rejectModal = true; },
            init() {}
        }));
    });
</script>
@endpush
@endsection
