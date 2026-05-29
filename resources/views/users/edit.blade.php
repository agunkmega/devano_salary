@extends('layouts.admin')

@section('page-title', 'Edit User')
@section('page-subtitle', 'Ubah data pengguna')

@section('page-content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-6">
        @csrf @method('PUT')

        @if($errors->any())
        <div class="p-3 text-sm text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/50 rounded-xl border border-red-200 dark:border-red-800">
            @foreach($errors->all() as $err)
            <p>{{ $err }}</p>
            @endforeach
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password Baru <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Role / Hak Akses</label>
                <select name="role" required class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-blue-500">
                    <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="hr" {{ old('role', $user->role) == 'hr' ? 'selected' : '' }}>HR</option>
                    <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">Simpan</button>
        </div>
    </form>
</div>
@endsection
