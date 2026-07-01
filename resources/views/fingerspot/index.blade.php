@extends('layouts.admin')

@section('page-title', 'Riwayat Webhook Finger Spot')
@section('page-subtitle', 'Data absensi yang masuk otomatis dari mesin')

@section('page-content')
<div x-data="webhookEdit()" class="space-y-6">
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total Data</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $today }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Hari Ini</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">✓</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Webhook Aktif</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nama</label>
                <input type="text" name="name" value="{{ request('name') }}" placeholder="Cari nama..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500 w-48">
            </div>
            <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Filter</button>
            <a href="{{ route('admin.fingerspot.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Reset</a>
            <a href="{{ route('admin.fingerspot.export', request()->query()) }}" class="px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
        </form>

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Masuk via Webhook</h3>
            <a href="{{ route('admin.settings.index', ['tab' => 'fingerspot']) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Pengaturan</a>
        </div>

        @if($recent->count() > 0)
        <div class="overflow-x-auto max-h-[60vh] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium w-10">No</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Waktu Masuk</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">NIK</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Clock In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Break Out</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Break In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Clock Out</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Lembur In</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Lembur Out</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent as $i => $a)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400 text-xs text-center">{{ $i + 1 }}</td>
                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400 text-xs">{{ $a->created_at->format('d M H:i') }}</td>
                        <td class="py-3 px-3 text-gray-900 dark:text-white font-mono text-xs">{{ $a->employee->nik ?? '-' }}</td>
                        <td class="py-3 px-3 text-gray-900 dark:text-white">{{ $a->employee->full_name ?? '-' }}</td>
                        <td class="py-3 px-3 text-gray-900 dark:text-white">{{ $a->attendance_date instanceof Carbon\Carbon ? $a->attendance_date->format('d M Y') : $a->attendance_date }}</td>
                        <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400 text-xs">{{ $a->clock_in ? Carbon\Carbon::parse($a->clock_in)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400 text-xs">{{ $a->break_out ? Carbon\Carbon::parse($a->break_out)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400 text-xs">{{ $a->break_in ? Carbon\Carbon::parse($a->break_in)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-blue-600 dark:text-blue-400 text-xs">{{ $a->clock_out ? Carbon\Carbon::parse($a->clock_out)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-orange-600 dark:text-orange-400 text-xs">{{ $a->overtime_in ? Carbon\Carbon::parse($a->overtime_in)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 font-mono text-orange-600 dark:text-orange-400 text-xs">{{ $a->overtime_out ? Carbon\Carbon::parse($a->overtime_out)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-3 text-center">
                            <button @click='openEdit({{ $a->id }}, "{{ $a->employee_id }}", "{{ $a->employee?->nik ?? "" }}", "{{ $a->employee?->full_name ?? "" }}", "{{ $a->attendance_date instanceof Carbon\Carbon ? $a->attendance_date->format("Y-m-d") : $a->attendance_date }}", "{{ $a->clock_in ? Carbon\Carbon::parse($a->clock_in)->format("H:i") : "" }}", "{{ $a->break_out ? Carbon\Carbon::parse($a->break_out)->format("H:i") : "" }}", "{{ $a->break_in ? Carbon\Carbon::parse($a->break_in)->format("H:i") : "" }}", "{{ $a->clock_out ? Carbon\Carbon::parse($a->clock_out)->format("H:i") : "" }}", "{{ $a->overtime_in ? Carbon\Carbon::parse($a->overtime_in)->format("H:i") : "" }}", "{{ $a->overtime_out ? Carbon\Carbon::parse($a->overtime_out)->format("H:i") : "" }}", "{{ $a->status }}")' class="p-2 rounded-lg transition-colors text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada data untuk periode ini.</p>
        @endif
    </div>

    {{-- Edit Modal --}}
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="editModal = false">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="editModal = false"></div>
        <div x-show="editModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Absensi</h3>
            <form @submit.prevent="submitEdit">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="employee_id" :value="editEmployeeId">
                <input type="hidden" name="attendance_date" :value="editDate">
                <div class="space-y-4">
                    <div class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                        <span x-text="editEmployeeName"></span> - <span x-text="editDate"></span>
                    </div>
                    <template x-if="editError">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl text-sm" x-text="editError"></div>
                    </template>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Clock In</label>
                            <input type="time" x-model="editForm.clock_in" name="clock_in" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Break Out</label>
                            <input type="time" x-model="editForm.break_out" name="break_out" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Break In</label>
                            <input type="time" x-model="editForm.break_in" name="break_in" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Clock Out</label>
                            <input type="time" x-model="editForm.clock_out" name="clock_out" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Lembur In</label>
                            <input type="time" x-model="editForm.overtime_in" name="overtime_in" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Lembur Out</label>
                            <input type="time" x-model="editForm.overtime_out" name="overtime_out" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                        <select x-model="editForm.status" name="status" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="cuti">Cuti</option>
                            <option value="alpha">Alpha</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700" x-bind:disabled="submitting">
                        <span x-show="!submitting">Update</span>
                        <span x-show="submitting">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('webhookEdit', () => ({
            editModal: false,
            editId: null,
            editEmployeeId: null,
            editEmployeeName: null,
            editDate: null,
            editForm: { clock_in: '', break_out: '', break_in: '', clock_out: '', overtime_in: '', overtime_out: '', status: '' },
            submitting: false,
            editError: '',
            openEdit(id, empId, nik, name, date, clockIn, breakOut, breakIn, clockOut, overtimeIn, overtimeOut, status) {
                this.editId = id;
                this.editEmployeeId = empId;
                this.editEmployeeName = name;
                this.editDate = date;
                this.editForm = { clock_in: clockIn, break_out: breakOut, break_in: breakIn, clock_out: clockOut, overtime_in: overtimeIn, overtime_out: overtimeOut, status: status };
                this.editError = '';
                this.editModal = true;
            },
            async submitEdit() {
                this.submitting = true;
                this.editError = '';
                const formData = new FormData();
                formData.append('_token', document.querySelector('input[name="_token"]').value);
                formData.append('_method', 'PUT');
                formData.append('employee_id', this.editEmployeeId);
                formData.append('attendance_date', this.editDate);
                formData.append('clock_in', this.editForm.clock_in || '');
                formData.append('break_out', this.editForm.break_out || '');
                formData.append('break_in', this.editForm.break_in || '');
                formData.append('clock_out', this.editForm.clock_out || '');
                formData.append('overtime_in', this.editForm.overtime_in || '');
                formData.append('overtime_out', this.editForm.overtime_out || '');
                formData.append('status', this.editForm.status);
                try {
                    const resp = await fetch(`/admin/attendances/${this.editId}`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                    });
                    const json = await resp.json();
                    if (resp.ok && json.success) {
                        this.editModal = false;
                        window.location.reload();
                    } else {
                        const msg = json.errors ? Object.values(json.errors).flat().join(', ') : json.message;
                        this.editError = msg || 'Update gagal';
                    }
                } catch (e) {
                    this.editError = 'Terjadi kesalahan koneksi';
                } finally {
                    this.submitting = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection
