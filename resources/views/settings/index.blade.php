@extends('layouts.admin')

@section('page-title', 'Pengaturan')
@section('page-subtitle', 'Konfigurasi aplikasi')

@section('page-content')
@if(session('success'))
<div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
@endif
<div x-data="settings()" x-init="init()" class="max-w-4xl mx-auto space-y-6">
    <div class="flex gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl w-fit">
        <button @click="activeTab = 'general'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'general' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Umum</button>
        <button @click="activeTab = 'payroll'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'payroll' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Payroll</button>
        <button @click="activeTab = 'attendance'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'attendance' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Absensi</button>
        <button @click="activeTab = 'database'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'database' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Database</button>
        <button @click="activeTab = 'email'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'email' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Email</button>
        <button @click="activeTab = 'fingerspot'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'fingerspot' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Finger Spot</button>
        <button @click="activeTab = 'mobile_api'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all flex items-center gap-1.5" :class="activeTab === 'mobile_api' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            Mobile API
        </button>
    </div>

    <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Perusahaan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Aplikasi</label>
                    <input type="text" name="settings[app_name]" value="{{ $settings->get('company')?->firstWhere('key', 'app_name')?->value ?? config('app.name') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Perusahaan</label>
                    <input type="text" name="settings[company_name]" value="{{ $settings->get('company')?->firstWhere('key', 'company_name')?->value ?? 'PT. Nama Perusahaan' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email Perusahaan</label>
                    <input type="email" name="settings[company_email]" value="{{ $settings->get('company')?->firstWhere('key', 'company_email')?->value ?? 'info@perusahaan.com' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Telepon</label>
                    <input type="text" name="settings[company_phone]" value="{{ $settings->get('company')?->firstWhere('key', 'company_phone')?->value ?? '(021) 1234567' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Website</label>
                    <input type="url" name="settings[company_website]" value="{{ $settings->get('company')?->firstWhere('key', 'company_website')?->value ?? 'https://perusahaan.com' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat</label>
                    <textarea name="settings[company_address]" rows="3" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">{{ $settings->get('company')?->firstWhere('key', 'company_address')?->value ?? 'Jl. Contoh No. 123, Kelurahan, Kecamatan, Kota, Provinsi' }}</textarea>
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Simpan</button></div>
        </form>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logo Perusahaan</h3>
            @php $logoPath = $settings->get('company')?->firstWhere('key', 'app_logo')?->value; @endphp
            <div class="flex items-center gap-4">
                @if($logoPath)
                <div class="relative">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="Logo" class="h-12 w-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <form method="POST" action="{{ route('admin.settings.logo.delete') }}" class="absolute -top-2 -right-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">&times;</button>
                    </form>
                </div>
                @endif
                <form method="POST" action="{{ route('admin.settings.logo.upload') }}" enctype="multipart/form-data" class="flex items-center gap-3">
                    @csrf
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml" class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100">
                    <button type="submit" class="px-4 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Upload</button>
                </form>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Format: PNG, JPG, SVG. Maks 2MB.</p>
        </div>
    </div>

    <div x-show="activeTab === 'payroll'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pengaturan Payroll</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Ketenagakerjaan Full Tanggungan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="text" inputmode="decimal" name="settings[bpjs_ketenagakerjaan_full_rate]" value="{{ $settings->get('payroll')?->firstWhere('key', 'bpjs_ketenagakerjaan_full_rate')?->value ?? '0' }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Ketenagakerjaan Kecelakaan & Kematian (Rp)</label>
                    <select name="settings[bpjs_ketenagakerjaan_partial_rate]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                        @php $partialVal = $settings->get('payroll')?->firstWhere('key', 'bpjs_ketenagakerjaan_partial_rate')?->value ?? '10000'; @endphp
                        <option value="0" {{ $partialVal == '0' ? 'selected' : '' }}>Tidak Ada</option>
                        <option value="10000" {{ $partialVal == '10000' ? 'selected' : '' }}>Rp 10.000</option>
                        <option value="14000" {{ $partialVal == '14000' ? 'selected' : '' }}>Rp 14.000</option>
                        <option value="20000" {{ $partialVal == '20000' ? 'selected' : '' }}>Rp 20.000</option>
                        <option value="30000" {{ $partialVal == '30000' ? 'selected' : '' }}>Rp 30.000</option>
                        <option value="50000" {{ $partialVal == '50000' ? 'selected' : '' }}>Rp 50.000</option>
                        <option value="100000" {{ $partialVal == '100000' ? 'selected' : '' }}>Rp 100.000</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Ket. Full — Porsi Perusahaan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="text" inputmode="decimal" name="settings[bpjs_ket_full_company]" value="{{ $settings->get('payroll')?->firstWhere('key', 'bpjs_ket_full_company')?->value ?? '0' }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Ket. Partial — Porsi Karyawan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="text" inputmode="decimal" name="settings[bpjs_ket_partial_employee]" value="{{ $settings->get('payroll')?->firstWhere('key', 'bpjs_ket_partial_employee')?->value ?? '0' }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Ket. Partial — Porsi Perusahaan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="text" inputmode="decimal" name="settings[bpjs_ket_partial_company]" value="{{ $settings->get('payroll')?->firstWhere('key', 'bpjs_ket_partial_company')?->value ?? '10000' }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Kesehatan — Porsi Karyawan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="text" inputmode="decimal" name="settings[bpjs_kesehatan_rate]" value="{{ $settings->get('payroll')?->firstWhere('key', 'bpjs_kesehatan_rate')?->value ?? '0' }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Kesehatan — Porsi Perusahaan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="text" inputmode="decimal" name="settings[bpjs_kesehatan_company]" value="{{ $settings->get('payroll')?->firstWhere('key', 'bpjs_kesehatan_company')?->value ?? '26500' }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">PPh 21 (%)</label>
                    <input type="number" step="0.1" name="settings[tax_rate]" value="{{ $settings->get('payroll')?->firstWhere('key', 'tax_rate')?->value ?? '5' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Iuran Wajib (Rp)</label>
                    <select name="settings[iuran_wajib_amount]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                        @php $iwVal = $settings->get('payroll')?->firstWhere('key', 'iuran_wajib_amount')?->value ?? '50000'; @endphp
                        <option value="0" {{ $iwVal == '0' ? 'selected' : '' }}>Tidak Ada</option>
                        <option value="25000" {{ $iwVal == '25000' ? 'selected' : '' }}>Rp 25.000</option>
                        <option value="50000" {{ $iwVal == '50000' ? 'selected' : '' }}>Rp 50.000</option>
                        <option value="75000" {{ $iwVal == '75000' ? 'selected' : '' }}>Rp 75.000</option>
                        <option value="100000" {{ $iwVal == '100000' ? 'selected' : '' }}>Rp 100.000</option>
                        <option value="150000" {{ $iwVal == '150000' ? 'selected' : '' }}>Rp 150.000</option>
                        <option value="200000" {{ $iwVal == '200000' ? 'selected' : '' }}>Rp 200.000</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Simpan</button></div>
        </form>
    </div>

    <div x-show="activeTab === 'attendance'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
        <form class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pengaturan Absensi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Denda Keterlambatan (per menit)</label>
                    <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                    <input type="number" value="25000" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Toleransi Keterlambatan (menit)</label>
                    <input type="number" value="15" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Maksimum Hari Kerja per Bulan</label>
                    <input type="number" value="22" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Batas Input Absensi (hari)</label>
                    <input type="number" value="7" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Simpan</button></div>
        </form>
    </div>

    <div x-show="activeTab === 'database'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Backup Database</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Backup database untuk mencegah kehilangan data. Disarankan melakukan backup secara rutin.</p>
            <div class="flex items-center gap-4">
                <form method="POST" action="{{ route('admin.settings.backup') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Backup Sekarang
                    </button>
                </form>
                <div class="text-sm text-gray-400">
                    @if(count($backups) > 0)
                    Terakhir backup: {{ \Carbon\Carbon::createFromTimestamp($backups[0]['date'])->format('d M Y H:i') }}
                    @else
                    Belum ada backup
                    @endif
                </div>
            </div>
            @if(count($backups) > 0)
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Riwayat Backup</h4>
                <div class="space-y-2">
                    @foreach($backups as $b)
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $b['filename'] }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::createFromTimestamp($b['date'])->format('d M Y, H:i') }} &middot; {{ round($b['size'] / 1024) }} KB</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.settings.backup.download', basename($b['filename'])) }}" class="text-blue-600 dark:text-blue-400 text-sm hover:underline">Download</a>
                            <form method="POST" action="{{ route('admin.settings.backup.delete', basename($b['filename'])) }}" onsubmit="return confirm('Hapus backup ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 text-sm hover:underline">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div x-show="activeTab === 'email'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pengaturan Email (SMTP)</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Konfigurasi SMTP untuk mengirim slip gaji via email.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Host</label>
                    <input type="text" name="settings[mail_host]" value="{{ $settings->get('email')?->firstWhere('key', 'mail_host')?->value ?? 'mail.devanosilver.com' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Port</label>
                    <input type="text" name="settings[mail_port]" value="{{ $settings->get('email')?->firstWhere('key', 'mail_port')?->value ?? '465' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Username</label>
                    <input type="text" name="settings[mail_username]" value="{{ $settings->get('email')?->firstWhere('key', 'mail_username')?->value ?? '' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">SMTP Password</label>
                    <input type="password" name="settings[mail_password]" value="{{ $settings->get('email')?->firstWhere('key', 'mail_password')?->value ?? '' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Enkripsi</label>
                    <select name="settings[mail_encryption]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                        @php $encVal = $settings->get('email')?->firstWhere('key', 'mail_encryption')?->value ?? 'ssl'; @endphp
                        <option value="ssl" {{ $encVal == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="tls" {{ $encVal == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="" {{ $encVal == '' ? 'selected' : '' }}>Tanpa Enkripsi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat Email Pengirim</label>
                    <input type="email" name="settings[mail_from_address]" value="{{ $settings->get('email')?->firstWhere('key', 'mail_from_address')?->value ?? '' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Pengirim</label>
                    <input type="text" name="settings[mail_from_name]" value="{{ $settings->get('email')?->firstWhere('key', 'mail_from_name')?->value ?? config('app.name') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>

    <div x-show="activeTab === 'fingerspot'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Finger Spot API</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Konfigurasi API Finger Spot untuk mengambil data absensi.</p>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">API URL</label>
                    <input type="text" name="settings[fingerspot_api_url]" value="{{ $settings->get('fingerspot')?->firstWhere('key', 'fingerspot_api_url')?->value ?? 'https://api.fingerspot.io/api/v1' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="https://api.fingerspot.io/api/v1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">API Token</label>
                    <input type="password" name="settings[fingerspot_api_token]" value="{{ $settings->get('fingerspot')?->firstWhere('key', 'fingerspot_api_token')?->value ?? '' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Device ID</label>
                    <input type="text" name="settings[fingerspot_device_id]" value="{{ $settings->get('fingerspot')?->firstWhere('key', 'fingerspot_device_id')?->value ?? '' }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="Serial number mesin">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>

        <div x-show="activeTab === 'mobile_api'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Header Section -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Pengaturan Mobile API</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Konfigurasi autentikasi, verifikasi akun, fitur chat, dan integrasi WhatsApp aplikasi mobile.</p>
                    </div>
                </div>

                <!-- Sub-tabs navigation -->
                <div class="flex gap-2 mt-5 p-1 bg-gray-100 dark:bg-gray-900/50 rounded-xl w-fit flex-wrap">
                    <button type="button" @click="mobileSubTab = 'auth'" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all flex items-center gap-2" :class="mobileSubTab === 'auth' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Autentikasi & Verifikasi
                    </button>
                                        <button type="button" @click="mobileSubTab = 'online_attendance'" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all flex items-center gap-2" :class="mobileSubTab === 'online_attendance' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Presensi Online & GPS
                    </button>
                    <button type="button" @click="mobileSubTab = 'chat'" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all flex items-center gap-2" :class="mobileSubTab === 'chat' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        Fitur Chat & Hak Akses
                    </button>
                    <button type="button" @click="mobileSubTab = 'whatsapp'" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all flex items-center gap-2" :class="mobileSubTab === 'whatsapp' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        WhatsApp Gateway
                    </button>
                </div>
            </div>

            <!-- SUBTAB 1: AUTENTIKASI & VERIFIKASI -->
            <div x-show="mobileSubTab === 'auth'" class="space-y-6">
                <!-- Section 1: Metode Verifikasi Aktivasi Karyawan -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold">1</span>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Metode Verifikasi Aktivasi Akun</h4>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 ml-8">Pilih alur verifikasi saat karyawan pertama kali mengaktifkan akun di aplikasi mobile.</p>

                    @php $authVerifyMethod = $settings->get('mobile_api')?->firstWhere('key', 'mobile_auth_verification_method')?->value ?? 'dual_key'; @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-8">
                        <!-- Option 1: Dual-Key (Default) -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="authVerifyMethod === 'dual_key' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    Dual-Key Matching (Non-OTP / Default)
                                </span>
                                <input type="radio" name="settings[mobile_auth_verification_method]" value="dual_key" x-model="authVerifyMethod" {{ $authVerifyMethod === 'dual_key' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Verifikasi instan mencocokkan NIP Pegawai + NIK KTP dengan database HRD. Tanpa biaya SMS/Gateway.</p>
                        </label>

                        <!-- Option 2: Email OTP -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="authVerifyMethod === 'email_otp' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    NIP + NIK KTP + OTP Email
                                </span>
                                <input type="radio" name="settings[mobile_auth_verification_method]" value="email_otp" x-model="authVerifyMethod" {{ $authVerifyMethod === 'email_otp' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Kirim 6-digit kode OTP ke email karyawan via server SMTP yang sudah dikonfigurasi pada tab Email.</p>
                        </label>

                        <!-- Option 3: WhatsApp OTP -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="authVerifyMethod === 'whatsapp_otp' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    NIP + NIK KTP + OTP WhatsApp
                                </span>
                                <input type="radio" name="settings[mobile_auth_verification_method]" value="whatsapp_otp" x-model="authVerifyMethod" {{ $authVerifyMethod === 'whatsapp_otp' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Kirim kode OTP ke nomor WhatsApp terdaftar karyawan menggunakan integrasi WhatsApp Gateway.</p>
                        </label>

                        <!-- Option 4: Hybrid OTP -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="authVerifyMethod === 'hybrid_otp' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                    Pilihan Bebas (Email atau WhatsApp)
                                </span>
                                <input type="radio" name="settings[mobile_auth_verification_method]" value="hybrid_otp" x-model="authVerifyMethod" {{ $authVerifyMethod === 'hybrid_otp' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan dapat memilih menerima kode OTP melalui Email atau WhatsApp.</p>
                        </label>
                    </div>
                </div>

                <!-- Parameter OTP (Hanya Tampil Jika Mode OTP Dipilih) -->
                <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700" x-show="authVerifyMethod !== 'dual_key'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold">2</span>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Parameter Kode OTP</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 ml-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Panjang Kode OTP</label>
                            @php $otpLen = $settings->get('mobile_api')?->firstWhere('key', 'mobile_otp_length')?->value ?? '6'; @endphp
                            <select name="settings[mobile_otp_length]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="4" {{ $otpLen == '4' ? 'selected' : '' }}>4 Digit</option>
                                <option value="6" {{ $otpLen == '6' ? 'selected' : '' }}>6 Digit (Standar Rekomendasi)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Masa Berlaku OTP (Menit)</label>
                            @php $otpExp = $settings->get('mobile_api')?->firstWhere('key', 'mobile_otp_expiry_minutes')?->value ?? '5'; @endphp
                            <select name="settings[mobile_otp_expiry_minutes]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="3" {{ $otpExp == '3' ? 'selected' : '' }}>3 Menit</option>
                                <option value="5" {{ $otpExp == '5' ? 'selected' : '' }}>5 Menit (Rekomendasi)</option>
                                <option value="10" {{ $otpExp == '10' ? 'selected' : '' }}>10 Menit</option>
                                <option value="15" {{ $otpExp == '15' ? 'selected' : '' }}>15 Menit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Maksimal Percobaan Salah</label>
                            @php $otpMax = $settings->get('mobile_api')?->firstWhere('key', 'mobile_otp_max_attempts')?->value ?? '3'; @endphp
                            <select name="settings[mobile_otp_max_attempts]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="3" {{ $otpMax == '3' ? 'selected' : '' }}>3 Kali Percobaan</option>
                                <option value="5" {{ $otpMax == '5' ? 'selected' : '' }}>5 Kali Percobaan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Info Box Non-OTP -->
                <div x-show="authVerifyMethod === 'dual_key'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" class="ml-8 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 text-xs text-blue-700 dark:text-blue-300 flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Mode <strong>Dual-Key Matching (Non-OTP)</strong> aktif. Aktivasi akun diproses secara instan menggunakan pencocokan NIP Karyawan dan NIK KTP terhadap database master tanpa memerlukan pengiriman kode OTP.</span>
                </div>

                <!-- Reset Password & Keamanan Perangkat -->
                <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold">3</span>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Reset Password & Keamanan Perangkat</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 ml-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Metode Reset Password Mobile</label>
                            @php $resetMethod = $settings->get('mobile_api')?->firstWhere('key', 'mobile_reset_password_method')?->value ?? 'email_otp'; @endphp
                            <select name="settings[mobile_reset_password_method]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="email_otp" {{ $resetMethod == 'email_otp' ? 'selected' : '' }}>Kirim Kode OTP ke Email</option>
                                <option value="whatsapp_otp" {{ $resetMethod == 'whatsapp_otp' ? 'selected' : '' }}>Kirim Kode OTP ke WhatsApp</option>
                                <option value="email_link" {{ $resetMethod == 'email_link' ? 'selected' : '' }}>Kirim Tautan / Link Reset ke Email</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kebijakan Multi-Device Login</label>
                            @php $multiDev = $settings->get('mobile_api')?->firstWhere('key', 'mobile_allow_multidevice')?->value ?? '1'; @endphp
                            <select name="settings[mobile_allow_multidevice]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="1" {{ $multiDev == '1' ? 'selected' : '' }}>Izinkan Login di Banyak Perangkat</option>
                                <option value="0" {{ $multiDev == '0' ? 'selected' : '' }}>Hanya 1 Perangkat Aktif (Single Device)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- SUBTAB: PRESENSI ONLINE & GPS -->
            <div x-show="mobileSubTab === 'online_attendance'" class="space-y-6">
                <!-- Status Presensi Online -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold">1</span>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Status Fitur Presensi Online (Clock In / Out Mobile)</h4>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 ml-8">Kontrol apakah karyawan diizinkan melakukan absensi online langsung dari smartphone atau wajib menggunakan mesin fingerprint kantor.</p>

                    @php $onlineAttEnabled = $settings->get('mobile_api')?->firstWhere('key', 'mobile_online_attendance_enabled')?->value ?? '1'; @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-8">
                        <!-- Option 1: Aktif / Enabled -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="onlineAttEnabled === '1' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Presensi Online Aktif (Enable)
                                </span>
                                <input type="radio" name="settings[mobile_online_attendance_enabled]" value="1" x-model="onlineAttEnabled" {{ $onlineAttEnabled == '1' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan dapat melakukan Clock In & Clock Out langsung melalui aplikasi mobile menggunakan GPS dan verifikasi selfie.</p>
                        </label>

                        <!-- Option 2: Nonaktif / Disabled -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-red-500" :class="onlineAttEnabled === '0' ? 'border-red-500 bg-red-50/50 dark:bg-red-900/20 ring-2 ring-red-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    Presensi Online Nonaktif (Disable)
                                </span>
                                <input type="radio" name="settings[mobile_online_attendance_enabled]" value="0" x-model="onlineAttEnabled" {{ $onlineAttEnabled == '0' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tombol presensi di aplikasi dinonaktifkan. Seluruh absensi kehadiran wajib dilakukan melalui mesin fingerprint kantor.</p>
                        </label>
                    </div>
                </div>

                <!-- Konfigurasi Geofence & Selfie -->
                <div x-show="onlineAttEnabled === '1'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold">2</span>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Validasi Lokasi GPS & Foto Selfie</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 ml-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Validasi Geofence (Radius Kantor)</label>
                            @php $geofenceReq = $settings->get('mobile_api')?->firstWhere('key', 'mobile_attendance_geofence_enabled')?->value ?? '1'; @endphp
                            <select name="settings[mobile_attendance_geofence_enabled]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="1" {{ $geofenceReq == '1' ? 'selected' : '' }}>Wajib Dalam Radius Kantor (100m)</option>
                                <option value="0" {{ $geofenceReq == '0' ? 'selected' : '' }}>Bebas / Fleksibel (WFH / Lapangan)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Verifikasi Foto Selfie Kamera</label>
                            @php $selfieReq = $settings->get('mobile_api')?->firstWhere('key', 'mobile_attendance_selfie_required')?->value ?? '1'; @endphp
                            <select name="settings[mobile_attendance_selfie_required]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="1" {{ $selfieReq == '1' ? 'selected' : '' }}>Wajib Ambil Foto Selfie Live</option>
                                <option value="0" {{ $selfieReq == '0' ? 'selected' : '' }}>Tanpa Foto Selfie (Langsung Tap)</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pesan Penjelasan saat Presensi Online Nonaktif</label>
                            @php $disabledMsg = $settings->get('mobile_api')?->firstWhere('key', 'mobile_attendance_disabled_message')?->value ?? 'Presensi online saat ini dinonaktifkan oleh administrator. Silakan lakukan absensi melalui mesin fingerprint kantor.'; @endphp
                            <input type="text" name="settings[mobile_attendance_disabled_message]" value="{{ $disabledMsg }}" placeholder="Pesan untuk karyawan..." class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUBTAB 2: FITUR CHAT & HAK AKSES -->
            <div x-show="mobileSubTab === 'chat'" class="space-y-6">
                <!-- Pengaturan Hak Akses / Chat ke Siapa Saja -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold">1</span>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Hak Akses & Cakupan Obrolan (Siapa Saja yang Bisa Di-Chat)</h4>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 ml-8">Tentukan batasan karyawan dalam melihat dan menghubungi rekan kerja di direktori chat.</p>

                    @php $chatScope = $settings->get('mobile_api')?->firstWhere('key', 'mobile_chat_permission_scope')?->value ?? 'all'; @endphp
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 ml-8">
                        <!-- Option 1: Semua Karyawan -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="'{{ $chatScope }}' === 'all' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Semua Karyawan (Bebas)
                                </span>
                                <input type="radio" name="settings[mobile_chat_permission_scope]" value="all" {{ $chatScope === 'all' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Seluruh karyawan dapat mencari dan mengirim pesan ke siapapun di seluruh divisi perusahaan.</p>
                        </label>

                        <!-- Option 2: Satu Departemen + HRD + IT -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="'{{ $chatScope }}' === 'department_and_hr_it' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                    Satu Departemen + HRD + IT
                                </span>
                                <input type="radio" name="settings[mobile_chat_permission_scope]" value="department_and_hr_it" {{ $chatScope === 'department_and_hr_it' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan dapat chat dengan rekan satu divisi + perwakilan HRD + tim teknis & IT.</p>
                        </label>

                        <!-- Option 3: Satu Departemen + HRD -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="'{{ $chatScope }}' === 'department_and_hr' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    Satu Departemen & HRD
                                </span>
                                <input type="radio" name="settings[mobile_chat_permission_scope]" value="department_and_hr" {{ $chatScope === 'department_and_hr' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan hanya bisa chat dengan rekan satu divisi serta perwakilan tim HRD & Admin.</p>
                        </label>

                        <!-- Option 4: Hanya Satu Departemen -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="'{{ $chatScope }}' === 'same_department' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    Hanya Sesama Departemen
                                </span>
                                <input type="radio" name="settings[mobile_chat_permission_scope]" value="same_department" {{ $chatScope === 'same_department' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan hanya dapat melihat dan mengirim pesan ke rekan dalam satu unit kerja yang sama.</p>
                        </label>

                        <!-- Option 5: Hanya HRD & IT (Helpdesk) -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="'{{ $chatScope }}' === 'hr_and_it_only' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    Hanya HRD & Tim IT (Helpdesk)
                                </span>
                                <input type="radio" name="settings[mobile_chat_permission_scope]" value="hr_and_it_only" {{ $chatScope === 'hr_and_it_only' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan hanya dapat chat untuk bantuan resmi ke tim HRD & Tim Teknis / IT.</p>
                        </label>

                        <!-- Option 6: Hanya HRD / Manajemen -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-blue-500" :class="'{{ $chatScope }}' === 'hr_admin_only' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Hanya HRD / Manajemen
                                </span>
                                <input type="radio" name="settings[mobile_chat_permission_scope]" value="hr_admin_only" {{ $chatScope === 'hr_admin_only' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan hanya dapat mengajukan pertanyaan/pesan secara resmi ke HRD / Manajemen.</p>
                        </label>

                        <!-- Option 7: Nonaktifkan Chat (Disable) -->
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all hover:border-red-500" :class="'{{ $chatScope }}' === 'disabled' ? 'border-red-500 bg-red-50/50 dark:bg-red-900/20 ring-2 ring-red-500/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    Nonaktifkan Chat (Disable)
                                </span>
                                <input type="radio" name="settings[mobile_chat_permission_scope]" value="disabled" {{ $chatScope === 'disabled' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Matikan fitur obrolan sepenuhnya di aplikasi mobile. Karyawan tidak dapat mengirim pesan.</p>
                        </label>
                    </div>
                </div>

                <!-- Konfigurasi Fitur Chat -->
                <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold">2</span>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Format & Perilaku Obrolan</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 ml-8">
                                                <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Judul Modul Chat di Aplikasi Mobile</label>
                            @php $chatTitle = $settings->get('mobile_api')?->firstWhere('key', 'mobile_chat_title')?->value ?? ''; @endphp
                            <input type="text" name="settings[mobile_chat_title]" value="{{ $chatTitle }}" placeholder="Kosongkan untuk otomatis (misal: Pusat Bantuan & HRD)" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status Fitur Chat Mobile</label>
                            @php $chatEnabled = $settings->get('mobile_api')?->firstWhere('key', 'mobile_chat_enabled')?->value ?? '1'; @endphp
                            <select name="settings[mobile_chat_enabled]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="1" {{ $chatEnabled == '1' ? 'selected' : '' }}>Aktif (Fitur Chat dapat diakses)</option>
                                <option value="0" {{ $chatEnabled == '0' ? 'selected' : '' }}>Nonaktif (Sembunyikan menu Chat)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mode Komunikasi Chat</label>
                            @php $chatMode = $settings->get('mobile_api')?->firstWhere('key', 'mobile_chat_mode')?->value ?? 'hybrid'; @endphp
                            <select name="settings[mobile_chat_mode]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="hybrid" {{ $chatMode == 'hybrid' ? 'selected' : '' }}>Hybrid (Chat Internal + Tombol WhatsApp Direct)</option>
                                <option value="internal_only" {{ $chatMode == 'internal_only' ? 'selected' : '' }}>Internal Chat Saja (Database App)</option>
                                <option value="whatsapp_only" {{ $chatMode == 'whatsapp_only' ? 'selected' : '' }}>WhatsApp Direct Saja (Buka Aplikasi WA)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Izinkan Lampiran Gambar / Foto</label>
                            @php $chatAllowImg = $settings->get('mobile_api')?->firstWhere('key', 'mobile_chat_allow_images')?->value ?? '1'; @endphp
                            <select name="settings[mobile_chat_allow_images]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="1" {{ $chatAllowImg == '1' ? 'selected' : '' }}>Izinkan Kirim Foto / Screenshot</option>
                                <option value="0" {{ $chatAllowImg == '0' ? 'selected' : '' }}>Hanya Teks (Tanpa Gambar)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Maksimal Panjang Karakter Pesan</label>
                            @php $chatMaxLen = $settings->get('mobile_api')?->firstWhere('key', 'mobile_chat_max_length')?->value ?? '1000'; @endphp
                            <select name="settings[mobile_chat_max_length]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="500" {{ $chatMaxLen == '500' ? 'selected' : '' }}>500 Karakter</option>
                                <option value="1000" {{ $chatMaxLen == '1000' ? 'selected' : '' }}>1.000 Karakter (Standar)</option>
                                <option value="2000" {{ $chatMaxLen == '2000' ? 'selected' : '' }}>2.000 Karakter</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pesan Pembuka / Sapaan Otomatis (Greeting Message)</label>
                            <input type="text" name="settings[mobile_chat_welcome_message]" value="{{ $settings->get('mobile_api')?->firstWhere('key', 'mobile_chat_welcome_message')?->value ?? 'Halo! Silakan mulai percakapan atau diskusi pekerjaan di sini.' }}" placeholder="Pesan sapaan pembuka..." class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUBTAB 3: WHATSAPP GATEWAY -->
            <div x-show="mobileSubTab === 'whatsapp'" class="space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs font-bold">1</span>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Konfigurasi WhatsApp Gateway Provider</h4>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 ml-8">Dibutuhkan jika mengaktifkan verifikasi WhatsApp OTP atau integrasi notifikasi gateway.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 ml-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">WhatsApp Gateway Provider</label>
                            @php $waProvider = $settings->get('mobile_api')?->firstWhere('key', 'mobile_wa_provider')?->value ?? 'fonnte'; @endphp
                            <select name="settings[mobile_wa_provider]" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="fonnte" {{ $waProvider == 'fonnte' ? 'selected' : '' }}>Fonnte (https://api.fonnte.com/send)</option>
                                <option value="wablas" {{ $waProvider == 'wablas' ? 'selected' : '' }}>Wablas (https://wablas.com)</option>
                                <option value="custom" {{ $waProvider == 'custom' ? 'selected' : '' }}>Custom Webhook / REST API</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">API Token / API Key</label>
                            <input type="password" name="settings[mobile_wa_api_token]" value="{{ $settings->get('mobile_api')?->firstWhere('key', 'mobile_wa_api_token')?->value ?? '' }}" placeholder="Contoh: eyJhbGciOi..." class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Custom Endpoint URL (Opsional)</label>
                            <input type="url" name="settings[mobile_wa_api_url]" value="{{ $settings->get('mobile_api')?->firstWhere('key', 'mobile_wa_api_url')?->value ?? '' }}" placeholder="https://api.gateway-anda.com/send" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                    Simpan Pengaturan Mobile API
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settings', () => ({
            activeTab: new URLSearchParams(window.location.search).get('tab') || 'general',
            mobileSubTab: new URLSearchParams(window.location.search).get('subtab') || 'auth',
            authVerifyMethod: '{{ $settings->get('mobile_api')?->firstWhere('key', 'mobile_auth_verification_method')?->value ?? 'dual_key' }}',
            onlineAttEnabled: '{{ $settings->get('mobile_api')?->firstWhere('key', 'mobile_online_attendance_enabled')?->value ?? '1' }}',
            init() {}
        }));
    });
</script>
@endpush
@endsection
