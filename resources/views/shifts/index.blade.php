@extends('layouts.admin')

@section('page-title', 'Shift')
@section('page-subtitle', 'Kelola jadwal shift')

@section('page-content')
<div x-data="shiftManager()" x-init="init()" class="space-y-6">
    <div class="flex justify-end">
        <button @click="openCreateModal()" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Shift
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Nama Shift</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Kode</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jam Masuk</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jam Keluar</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Toleransi (menit)</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="shift in shifts" :key="shift.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4">
                                <p class="text-gray-900 dark:text-white font-medium" x-text="shift.name"></p>
                            </td>
                            <td class="py-3 px-4 text-gray-500 dark:text-gray-400 font-mono text-xs" x-text="shift.code"></td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400 font-mono" x-text="shift.clock_in_time"></td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400 font-mono" x-text="shift.clock_out_time"></td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400" x-text="(shift.late_tolerance_minutes || 0) + ' menit'"></td>
                            <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-400" x-text="shift.employees_count || 0"></td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="shift.is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'" x-text="shift.is_active ? 'Aktif' : 'Non-Aktif'"></span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEditModal(shift)" class="p-2 text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="confirmDelete(shift)" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="modalOpen = false">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div x-show="modalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="editing ? 'Edit Shift' : 'Tambah Shift'"></h3>
            <form :action="editing ? `/admin/shifts/${editing.id}` : '{{ route('admin.shifts.store') }}'" method="POST">
                @csrf
                <template x-if="editing">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Shift <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name" name="name" @input="generateCode" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kode <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.code" name="code" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jam Masuk <span class="text-red-500">*</span></label>
                            <input type="time" x-model="form.clock_in_time" name="clock_in_time" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jam Keluar <span class="text-red-500">*</span></label>
                            <input type="time" x-model="form.clock_out_time" name="clock_out_time" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jam Keluar Sabtu</label>
                            <input type="time" x-model="form.saturday_clock_out_time" name="saturday_clock_out_time" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jam Istirahat</label>
                            <input type="time" x-model="form.break_start" name="break_start" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jam Selesai Istirahat</label>
                            <input type="time" x-model="form.break_end" name="break_end" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Toleransi Keterlambatan (menit)</label>
                        <input type="number" x-model="form.late_tolerance_minutes" name="late_tolerance_minutes" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="form.is_active" name="is_active" value="1" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        <label class="text-sm text-gray-700 dark:text-gray-300">Aktif</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors" x-text="editing ? 'Update' : 'Simpan'"></button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="deleteModal = false"></div>
        <div x-show="deleteModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Hapus Shift</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Yakin ingin menghapus shift <span class="font-semibold" x-text="deleting?.name"></span>?</p>
            <div class="flex gap-3 justify-center">
                <button @click="deleteModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                <form :action="`/admin/shifts/${deleting?.id}`" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('shiftManager', () => ({
            shifts: @json($shiftsData),
            modalOpen: false,
            deleteModal: false,
            editing: null,
            deleting: null,
            form: { name: '', code: '', clock_in_time: '', clock_out_time: '', saturday_clock_out_time: '', break_start: '', break_end: '', late_tolerance_minutes: 15, is_active: true },
            openCreateModal() { this.editing = null; this.form = { name: '', code: '', clock_in_time: '', clock_out_time: '', saturday_clock_out_time: '', break_start: '', break_end: '', late_tolerance_minutes: 15, is_active: true }; this.modalOpen = true; },
            openEditModal(shift) { this.editing = shift; this.form = { ...shift }; this.modalOpen = true; },
            confirmDelete(shift) { this.deleting = shift; this.deleteModal = true; },
            generateCode() { if (!this.editing && this.form.name) { this.form.code = this.form.name.substring(0, 3).toUpperCase(); } },
            init() {}
        }));
    });
</script>
@endpush
@endsection