@extends('layouts.admin')

@section('page-title', 'Edit Pengumuman')

@section('page-content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Pengumuman</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Perbarui rincian pengumuman atau ubah status penayangan.</p>
        </div>
        <a href="{{ route('admin.announcements.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
            Kembali
        </a>
    </div>

    <!-- Form Card -->
    <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <!-- Judul -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Judul Pengumuman <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Kategori & Prioritas Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" id="category" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="perusahaan" {{ old('category', $announcement->category) === 'perusahaan' ? 'selected' : '' }}>Perusahaan (Info Umum)</option>
                        <option value="penting" {{ old('category', $announcement->category) === 'penting' ? 'selected' : '' }}>Penting (Urgent / Broadcast)</option>
                        <option value="acara" {{ old('category', $announcement->category) === 'acara' ? 'selected' : '' }}>Acara (Gathering / Event)</option>
                        <option value="kebijakan" {{ old('category', $announcement->category) === 'kebijakan' ? 'selected' : '' }}>Kebijakan & Regulasi</option>
                        <option value="libur" {{ old('category', $announcement->category) === 'libur' ? 'selected' : '' }}>Hari Libur & Cuti Bersama</option>
                    </select>
                    @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="publish_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Jadwal Tanggal Tayang</label>
                    <input type="datetime-local" name="publish_date" id="publish_date" value="{{ old('publish_date', $announcement->publish_date ? $announcement->publish_date->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                    @error('publish_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Ringkasan Singkat / Snippet -->
            <div>
                <label for="snippet" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Ringkasan Singkat (Snippet)</label>
                <input type="text" name="snippet" id="snippet" value="{{ old('snippet', $announcement->snippet) }}" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                @error('snippet') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Banner / Gambar Ilustrasi -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Gambar / Banner Ilustrasi</label>
                @if($announcement->image)
                <div class="mb-3 flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <img src="{{ Storage::url($announcement->image) }}" alt="{{ $announcement->title }}" class="w-20 h-20 rounded-lg object-cover">
                    <div>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Gambar Saat Ini</p>
                        <label class="inline-flex items-center gap-2 mt-1 cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-gray-300">
                            <span class="text-xs text-red-600 font-medium">Hapus gambar ini</span>
                        </label>
                    </div>
                </div>
                @endif
                <input type="file" name="image" id="image" accept="image/*" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-300 hover:file:bg-blue-100">
                @error('image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Isi Lengkap Pengumuman -->
            <div>
                <label for="content" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Isi Lengkap Pengumuman <span class="text-red-500">*</span></label>
                <textarea name="content" id="content" rows="6" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">{{ old('content', $announcement->content) }}</textarea>
                @error('content') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Toggles: Is Important & Is Active -->
            <div class="pt-2 flex flex-col sm:flex-row gap-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_important" value="1" {{ old('is_important', $announcement->is_important) ? 'checked' : '' }} class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                    <div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Tandai sebagai Pengumuman Penting</span>
                        <p class="text-xs text-gray-500">Akan disorot di urutan paling atas dengan badge prioritas.</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }} class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                    <div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Tayangkan di Mobile</span>
                        <p class="text-xs text-gray-500">Aktifkan untuk menayangkan di beranda aplikasi.</p>
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
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection