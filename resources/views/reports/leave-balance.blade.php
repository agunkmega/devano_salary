@extends('layouts.admin')

@section('page-title', 'Sisa Cuti & DP')
@section('page-subtitle', 'Saldo cuti tahunan dan DP karyawan bulanan')

@section('page-content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form method="GET" action="{{ route('admin.reports.leave-balance') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Departemen</label>
                <select name="department_id" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Tampilkan</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sisa Cuti Tahunan & DP {{ now()->year }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Jabatan</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Quota CT</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">CT Terpakai</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Sisa CT</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Quota DP</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">DP Terpakai</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Sisa DP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $b)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-3 text-gray-900 dark:text-white font-medium">{{ $b['nama'] }}</td>
                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">{{ $b['jabatan'] }}</td>
                        <td class="py-3 px-3 text-center text-gray-900 dark:text-white">{{ $b['ct_quota'] }}</td>
                        <td class="py-3 px-3 text-center {{ $b['ct_used'] > 0 ? 'text-orange-600' : 'text-gray-900 dark:text-white' }}">{{ $b['ct_used'] }}</td>
                        <td class="py-3 px-3 text-center font-semibold {{ $b['ct_remaining'] == 0 ? 'text-red-600' : ($b['ct_remaining'] <= 3 ? 'text-orange-600' : 'text-emerald-600') }}">{{ $b['ct_remaining'] }}</td>
                        <td class="py-3 px-3 text-center text-gray-900 dark:text-white">{{ $b['dp_quota'] }}</td>
                        <td class="py-3 px-3 text-center {{ $b['dp_used'] > 0 ? 'text-orange-600' : 'text-gray-900 dark:text-white' }}">{{ $b['dp_used'] }}</td>
                        <td class="py-3 px-3 text-center font-semibold {{ $b['dp_remaining'] == 0 ? 'text-red-600' : ($b['dp_remaining'] <= 3 ? 'text-orange-600' : 'text-emerald-600') }}">{{ $b['dp_remaining'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
