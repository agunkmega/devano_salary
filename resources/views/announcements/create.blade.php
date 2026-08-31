@extends('layouts.admin')

@section('page-title', 'Buat Pengumuman Baru')

@section('page-content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buat Pengumuman Baru</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Publikasikan pengumuman untuk seluruh pegawai di aplikasi mobile.</p>
        </div>
        <a href="{{ route('admin.announcements.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
        @csrf

        <div class="space-y-4">
            <!-- Judul -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Judul Pengumuman <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="Contoh: Jadwal Libur Bersama Idul Fitri 1447 H" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Kategori & Prioritas Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" id="category" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="perusahaan" {{ old('category') === 'perusahaan' ? 'selected' : '' }}>Perusahaan (Info Umum)</option>
                        <option value="penting" {{ old('category') === 'penting' ? 'selected' : '' }}>Penting (Urgent / Broadcast)</option>
                        <option value="acara" {{ old('category') === 'acara' ? 'selected' : '' }}>Acara (Gathering / Event)</option>
                        <option value="kebijakan" {{ old('category') === 'kebijakan' ? 'selected' : '' }}>Kebijakan & Regulasi</option>
                        <option value="libur" {{ old('category') === 'libur' ? 'selected' : '' }}>Hari Libur & Cuti Bersama</option>
                    </select>
                    @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="publish_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Jadwal Tanggal Tayang</label>
                    <input type="datetime-local" name="publish_date" id="publish_date" value="{{ old('publish_date', now()->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                    @error('publish_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Ringkasan Singkat / Snippet -->
            <div>
                <label for="snippet" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Ringkasan Singkat (Snippet) <span class="text-xs font-normal text-gray-400">(Muncul di Carousel Home Mobile)</span></label>
                <input type="text" name="snippet" id="snippet" value="{{ old('snippet') }}" placeholder="Ringkasan 1-2 kalimat..." class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                @error('snippet') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Banner / Gambar Ilustrasi -->
            <div>
                <label for="image" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Gambar / Banner Ilustrasi <span class="text-xs font-normal text-gray-400">(Opsional, Max: 3MB)</span></label>
                <input type="file" name="image" id="image" accept="image/*" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-300 hover:file:bg-blue-100">
                @error('image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Isi Lengkap Pengumuman -->
            <div>
                <label for="content" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Isi Lengkap Pengumuman <span class="text-red-500">*</span></label>
                <textarea name="content" id="content" rows="6" required placeholder="Tuliskan isi pengumuman secara rinci di sini..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">{{ old('content') }}</textarea>
                @error('content') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Toggles: Is Important & Is Active -->
            <div class="pt-2 flex flex-col sm:flex-row gap-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_important" value="1" {{ old('is_important') ? 'checked' : '' }} class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                    <div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Tandai sebagai Pengumuman Penting</span>
                        <p class="text-xs text-gray-500">Akan disorot di urutan paling atas dengan badge prioritas.</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                    <div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Langsung Publikasikan</span>
                        <p class="text-xs text-gray-500">Pengumuman langsung tayang di aplikasi mobile.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('admin.announcements.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl shadow-md shadow-blue-500/20 transition-all">
                Simpan & Publikasikan
            </button>
        </div>
    </form>
</div>
@endsection