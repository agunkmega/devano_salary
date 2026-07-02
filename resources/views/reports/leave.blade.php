@extends('layouts.admin')

@section('page-title', 'Laporan Cuti & Izin')
@section('page-subtitle', 'Rekap cuti dan izin karyawan')

@section('page-content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form method="GET" action="{{ route('admin.reports.leave') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
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

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ $summary['pending'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Disetujui</p>
            <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $summary['approved'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Ditolak</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $summary['rejected'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Hari</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $summary['total_days'] }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Cuti & Izin</h3>
            <div class="flex gap-2">
                <button onclick="window.print()" class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Print</button>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height:calc(100vh - 280px)">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                    <tr>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Pegawai</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Departemen</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Jenis</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Hari</th>
                        <th class="text-center py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 px-4 text-gray-900 dark:text-white">{{ $leave->start_date->format('d M Y') }}{{ $leave->start_date->format('Y-m-d') !== $leave->end_date->format('Y-m-d') ? ' - ' . $leave->end_date->format('d M Y') : '' }}</td>
                            <td class="py-3 px-4"><span class="text-gray-900 dark:text-white font-medium">{{ $leave->employee?->full_name ?? $leave->employee?->user?->name ?? '-' }}</span></td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $leave->employee?->department?->name ?? '-' }}</td>
                            <td class="py-3 px-4">
                                @php $typeName = $leave->leaveType?->name ?? 'Cuti'; @endphp
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full @if(in_array($typeName, ['Cuti', 'Cuti Tahunan', 'Cuti Sakit'])) bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @else bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 @endif">{{ $typeName }}</span>
                            </td>
                            <td class="py-3 px-4 text-center text-gray-900 dark:text-white">{{ $leave->total_days }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full @switch($leave->status)
                                    @case('pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 @break
                                    @case('approved') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 @break
                                    @case('rejected') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @break
                                    @default bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400
                                @endswitch">{{ $leave->status === 'approved' ? 'Disetujui' : ($leave->status === 'pending' ? 'Pending' : ($leave->status === 'rejected' ? 'Ditolak' : 'Dibatalkan')) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $leaves->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
