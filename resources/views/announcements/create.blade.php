@extends('layouts.admin')

@section('page-title', 'Tambah Pengumuman')
@section('page-subtitle', 'Buat pengumuman baru')

@section('page-content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('admin.announcements.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror" placeholder="Contoh: Pengumuman Jadwal Gajian" required>
                    @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Isi Pengumuman <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="8" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror" placeholder="Tulis isi pengumuman..." required>{{ old('content') }}</textarea>
                    @error('content') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Aktif (tampil di portal pegawai)</span>
                    </label>
                </div>
            </div>
            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.announcements.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
