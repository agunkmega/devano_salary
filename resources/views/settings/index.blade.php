@extends('layouts.admin')

@section('page-title', 'Pengaturan')
@section('page-subtitle', 'Konfigurasi aplikasi')

@section('page-content')
<div x-data="settings()" x-init="init()" class="max-w-4xl mx-auto space-y-6">
    <div class="flex gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl w-fit">
        <button @click="activeTab = 'general'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'general' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Umum</button>
        <button @click="activeTab = 'payroll'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'payroll' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Payroll</button>
        <button @click="activeTab = 'attendance'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'attendance' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Absensi</button>
        <button @click="activeTab = 'database'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'database' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'">Database</button>
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
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Logo</label>
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
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: PNG, JPG, SVG. Maks 2MB.</p>
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Simpan</button></div>
        </form>
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">BPJS Kesehatan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="text" inputmode="decimal" name="settings[bpjs_kesehatan_rate]" value="{{ $settings->get('payroll')?->firstWhere('key', 'bpjs_kesehatan_rate')?->value ?? '0' }}" class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
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
                <button class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Backup Sekarang
                </button>
                <div class="text-sm text-gray-400">Terakhir backup: 15 Jan 2024</div>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Riwayat Backup</h4>
                <div class="space-y-2">
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div><p class="text-sm text-gray-900 dark:text-white">backup-2024-01-15.sql</p><p class="text-xs text-gray-500">15 Jan 2024, 10:30</p></div>
                        <button class="text-blue-600 dark:text-blue-400 text-sm hover:underline">Download</button>
                    </div>
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div><p class="text-sm text-gray-900 dark:text-white">backup-2024-01-01.sql</p><p class="text-xs text-gray-500">1 Jan 2024, 08:00</p></div>
                        <button class="text-blue-600 dark:text-blue-400 text-sm hover:underline">Download</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settings', () => ({
            activeTab: 'general',
            init() {}
        }));
    });
</script>
@endpush
@endsection
