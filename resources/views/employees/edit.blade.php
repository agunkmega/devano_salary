@extends('layouts.admin')

@section('page-title', 'Edit Pegawai')
@section('page-subtitle', $employee->full_name ?? 'Edit data pegawai')

@section('page-content')
<div x-data="employeeForm()" x-init="init()" class="max-w-4xl mx-auto">
    <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @php
            $prevUrl = url()->previous();
            $query = parse_url($prevUrl, PHP_URL_QUERY);
            parse_str($query ?? '', $prevParams);
            $prevPage = $prevParams['page'] ?? 1;
        @endphp
        @csrf
        @method('PUT')
        <input type="hidden" name="prev_page" value="{{ $prevPage }}">

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Data Pribadi
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">NIK <span class="text-red-500">*</span></label>
                    <input type="text" name="nik" value="{{ old('nik', $employee->nik) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('nik') border-red-500 @enderror">
                    @error('nik') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('full_name') border-red-500 @enderror">
                    @error('full_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror">
                    @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis Kelamin</label>
                    <select name="gender" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="L" {{ ($employee->gender ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ ($employee->gender ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Agama</label>
                    <select name="religion" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('religion') border-red-500 @enderror">
                        <option value="">Pilih Agama</option>
                        <option value="Islam" {{ ($employee->religion ?? '') === 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen" {{ ($employee->religion ?? '') === 'Kristen' ? 'selected' : '' }}>Kristen</option>
                        <option value="Katolik" {{ ($employee->religion ?? '') === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Hindu" {{ ($employee->religion ?? '') === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ ($employee->religion ?? '') === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Konghucu" {{ ($employee->religion ?? '') === 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                    </select>
                    @error('religion') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Departemen</label>
                    <select name="department_id" x-model="departmentId" @change="loadPositions" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Pilih Departemen</option>
                        @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" {{ ($employee->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jabatan</label>
                    <select name="position_id" x-model="positionId" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Pilih Jabatan</option>
                        <template x-for="pos in positions" :key="pos.id">
                            <option :value="pos.id" x-text="pos.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Shift</label>
                    <select name="shift_id" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Pilih Shift</option>
                        @foreach($shifts ?? [] as $shift)
                        <option value="{{ $shift->id }}" {{ ($employee->shift_id ?? '') == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Masuk</label>
                    <input type="date" name="join_date" value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                    <select name="is_active" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="1" {{ $employee->is_active ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !$employee->is_active ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis Pegawai</label>
                    <select name="employee_type" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="bulanan" {{ $employee->employee_type == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        <option value="harian" {{ $employee->employee_type == 'harian' ? 'selected' : '' }}>Harian</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Data Gaji
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Gaji Pokok</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" name="base_salary" value="{{ old('base_salary', $employee->base_salary ?? '') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tunjangan Absensi</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" name="allowance_absensi" value="{{ old('allowance_absensi', $employee->allowance_absensi ?? '0') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tunjangan Transport</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" name="allowance_transport" value="{{ old('allowance_transport', $employee->allowance_transport ?? '0') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tunjangan Jabatan</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" name="allowance_jabatan" value="{{ old('allowance_jabatan', $employee->allowance_jabatan ?? '0') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tunjangan Insentif</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" name="allowance_insentif" value="{{ old('allowance_insentif', $employee->allowance_insentif ?? '0') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Uang Lembur / Jam</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" name="overtime_pay_per_hour" value="{{ old('overtime_pay_per_hour', $employee->overtime_pay_per_hour ?? '0') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Uang Makan Lembur</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" name="uang_makan_lembur" value="{{ old('uang_makan_lembur', $employee->uang_makan_lembur ?? '0') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Diberikan jika lembur > 2 jam 29 menit</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="late_penalty_active" value="1" id="late_penalty_active" {{ old('late_penalty_active', $employee->late_penalty_active ?? false) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="late_penalty_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Potongan Keterlambatan 8% <span class="text-xs text-gray-400">(bulanan)</span></label>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Data Bank
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name ?? '') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. Rekening</label>
                    <input type="text" name="bank_account" value="{{ old('bank_account', $employee->bank_account ?? '') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Atas Nama</label>
                    <input type="text" name="bank_holder" value="{{ old('bank_holder', $employee->bank_holder ?? '') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Data BPJS
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Ketenagakerjaan</label>
                    <select name="bpjs_ketenagakerjaan_type" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Tidak Ikut</option>
                        <option value="full" {{ old('bpjs_ketenagakerjaan_type', $employee->bpjs_ketenagakerjaan_type) == 'full' ? 'selected' : '' }}>Full Tanggungan</option>
                        <option value="partial" {{ old('bpjs_ketenagakerjaan_type', $employee->bpjs_ketenagakerjaan_type) == 'partial' ? 'selected' : '' }}>Hanya Kecelakaan & Kematian</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Kesehatan</label>
                    <select name="bpjs_kesehatan_active" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="0" {{ old('bpjs_kesehatan_active', $employee->bpjs_kesehatan_active ?? false) ? '' : 'selected' }}>Tidak Aktif</option>
                        <option value="1" {{ old('bpjs_kesehatan_active', $employee->bpjs_kesehatan_active ?? false) ? 'selected' : '' }}>Aktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tambah Tanggungan BPJS Kesehatan</label>
                    <input type="number" name="bpjs_kesehatan_tanggungan" value="{{ old('bpjs_kesehatan_tanggungan', $employee->bpjs_kesehatan_tanggungan ?? '0') }}" min="0" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Jumlah tanggungan tambahan (orang tua, dll)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Iuran Wajib (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="number" name="iuran_wajib_amount" value="{{ old('iuran_wajib_amount', $employee->iuran_wajib_amount ?? '') }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. BPJS Ketenagakerjaan</label>
                    <input type="text" name="bpjs_ketenagakerjaan" value="{{ old('bpjs_ketenagakerjaan', $employee->bpjs_ketenagakerjaan ?? '') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Nomor BPJS Ketenagakerjaan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. BPJS Kesehatan</label>
                    <input type="text" name="bpjs_kesehatan" value="{{ old('bpjs_kesehatan', $employee->bpjs_kesehatan ?? '') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Nomor BPJS Kesehatan">
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Foto & Alamat
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Foto</label>
                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer" @click="$refs.photoInput.click()">
                        <template x-if="!photoPreview">
                            <div>
                                <template x-if="'{{ $employee->photo_url ?? "" }}'">
                                    <img src="{{ $employee->photo_url ?? '' }}" class="mx-auto w-32 h-32 object-cover rounded-xl">
                                </template>
                                <template x-if="!'{{ $employee->photo_url ?? "" }}'">
                                    <div>
                                        <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <p class="text-sm text-gray-500">Klik untuk upload foto</p>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="photoPreview">
                            <img :src="photoPreview" class="mx-auto w-32 h-32 object-cover rounded-xl">
                        </template>
                        <input type="file" x-ref="photoInput" name="photo" accept="image/*" class="hidden" @change="previewPhoto($event)">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat</label>
                    <textarea name="address" rows="4" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">{{ old('address', $employee->address ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.employees.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('employeeForm', () => ({
            departmentId: '{{ $employee->department_id ?? "" }}',
            positionId: '{{ $employee->position_id ?? "" }}',
            positions: [],
            allPositions: @json($positions),
            photoPreview: null,
            loadPositions() {
                this.positions = this.allPositions.filter(p => p.department_id == this.departmentId);
            },
            previewPhoto(event) {
                const file = event.target.files[0];
                if (file) { const reader = new FileReader(); reader.onload = e => this.photoPreview = e.target.result; reader.readAsDataURL(file); }
            },
            init() { if (this.departmentId) this.loadPositions(); }
        }));
    });
</script>
@endpush
@endsection
