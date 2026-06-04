@extends('layouts.admin')

@section('page-title', 'Jabatan')
@section('page-subtitle', 'Kelola data jabatan')

@section('page-content')
<div x-data="positionManager()" x-init="init()" class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <select x-model="filterDepartment" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Departemen</option>
                <template x-for="dept in departments" :key="dept.id">
                    <option :value="dept.id" x-text="dept.name"></option>
                </template>
            </select>
        </div>
        <button @click="openCreateModal()" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jabatan
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jabatan</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Kode</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Departemen</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Deskripsi</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="pos in filteredPositions" :key="pos.id">
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4">
                                <p class="text-gray-900 dark:text-white font-medium" x-text="pos.name"></p>
                            </td>
                            <td class="py-3 px-4 text-gray-500 dark:text-gray-400 font-mono text-xs" x-text="pos.code"></td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400" x-text="pos.department_name"></span>
                            </td>
                            <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-400" x-text="pos.employees_count || 0"></td>
                            <td class="py-3 px-4 text-gray-500 dark:text-gray-400 max-w-xs truncate" x-text="pos.description || '-'"></td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEditModal(pos)" class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="confirmDelete(pos)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 px-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ $positions->firstItem() }} - {{ $positions->lastItem() }} dari {{ $positions->total() }}
            </p>
            {{ $positions->withQueryString()->links() }}
        </div>
    </div>

    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="modalOpen = false">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div x-show="modalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="editing ? 'Edit Jabatan' : 'Tambah Jabatan'"></h3>
            <form :action="editing ? `/admin/positions/${editing.id}` : '{{ route('admin.positions.store') }}'" method="POST">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Jabatan <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name" name="name" @input="generateCode" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kode <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.code" name="code" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Departemen <span class="text-red-500">*</span></label>
                        <select x-model="form.department_id" name="department_id" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Pilih Departemen</option>
                            <template x-for="dept in departments" :key="dept.id">
                                <option :value="dept.id" x-text="dept.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
                        <textarea x-model="form.description" name="description" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700" x-text="editing ? 'Update' : 'Simpan'"></button>
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Hapus Jabatan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Yakin ingin menghapus jabatan <span class="font-semibold" x-text="deleting?.name"></span>?</p>
            <div class="flex gap-3 justify-center">
                <button @click="deleteModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                <form :action="`/admin/positions/${deleting?.id}`" method="POST">
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
        Alpine.data('positionManager', () => ({
            filterDepartment: '',
            departments: @json($departments),
            positions: @json($positionsData),
            modalOpen: false,
            deleteModal: false,
            editing: null,
            deleting: null,
            form: { name: '', code: '', department_id: '', description: '' },
            get filteredPositions() {
                if (!this.filterDepartment) return this.positions;
                return this.positions.filter(p => p.department_id == this.filterDepartment);
            },
            openCreateModal() { this.editing = null; this.form = { name: '', code: '', department_id: '', description: '' }; this.modalOpen = true; },
            openEditModal(pos) { this.editing = pos; this.form = { name: pos.name, code: pos.code, department_id: pos.department_id, description: pos.description }; this.modalOpen = true; },
            confirmDelete(pos) { this.deleting = pos; this.deleteModal = true; },
            generateCode() { if (!this.editing && this.form.name) { this.form.code = this.form.name.substring(0, 3).toUpperCase(); } },
            init() {}
        }));
    });
</script>
@endpush
@endsection