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
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nama Pegawai</label>
                <input type="text" name="employee" value="{{ request('employee') }}" placeholder="Cari nama pegawai..." class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">Tampilkan</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sisa Cuti Tahunan &amp; DP {{ $leaveYearLabel }}</h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">(26 Des {{ substr($leaveYearLabel, 0, 4) }} - 25 Des {{ substr($leaveYearLabel, 5) }})</span>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm" style="white-space:nowrap">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Nama</th>
                        <th class="text-left py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Jabatan</th>
                        <th class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-medium">Status</th>
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
                        <td class="py-3 px-3 text-center">
                            @if($b['cuti_eligible'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Berhak</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">Belum berhak</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center {{ !$b['cuti_eligible'] ? 'text-gray-400 line-through' : 'text-gray-900 dark:text-white' }}">{{ $b['ct_quota'] }}</td>
                        <td class="py-3 px-3 text-center {{ $b['ct_used'] > 0 ? 'text-orange-600' : 'text-gray-900 dark:text-white' }}">{{ $b['ct_used'] }}</td>
                        <td class="py-3 px-3 text-center font-semibold {{ !$b['cuti_eligible'] ? 'text-gray-400' : ($b['ct_remaining'] == 0 ? 'text-red-600' : ($b['ct_remaining'] <= 3 ? 'text-orange-600' : 'text-emerald-600')) }}">{{ $b['ct_remaining'] }}</td>
                        <td class="py-3 px-3 text-center {{ !$b['cuti_eligible'] ? 'text-gray-400 line-through' : 'text-gray-900 dark:text-white' }}">{{ $b['dp_quota'] }}</td>
                        <td class="py-3 px-3 text-center {{ $b['dp_used'] > 0 ? 'text-orange-600' : 'text-gray-900 dark:text-white' }}">{{ $b['dp_used'] }}</td>
                        <td class="py-3 px-3 text-center font-semibold {{ !$b['cuti_eligible'] ? 'text-gray-400' : ($b['dp_remaining'] == 0 ? 'text-red-600' : ($b['dp_remaining'] <= 3 ? 'text-orange-600' : 'text-emerald-600')) }}">{{ $b['dp_remaining'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
