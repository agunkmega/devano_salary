@extends('layouts.admin')

@section('page-title', 'Histori Kasbon')
@section('page-subtitle', $cashAdvance->employee?->full_name . ' - Rp ' . number_format($cashAdvance->amount, 0, ',', '.'))

@section('page-content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Kasbon</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $cashAdvance->employee?->full_name }}</p>
            </div>
            <span class="text-xs font-medium px-3 py-1.5 rounded-full {{ $cashAdvance->status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($cashAdvance->status === 'approved' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ($cashAdvance->status === 'rejected' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400')) }}">
                @switch($cashAdvance->status)
                    @case('paid') Lunas @break
                    @case('approved') Disetujui @break
                    @case('rejected') Ditolak @break
                    @default Pending
                @endswitch
            </span>
        </div>

        <div class="grid grid-cols-3 gap-4 text-center mb-6">
            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Pinjaman</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($cashAdvance->amount, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                <p class="text-xs text-gray-500 dark:text-gray-400">Cicilan</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $cashAdvance->installment_count }}x (Rp {{ number_format($cashAdvance->installment_amount, 0, ',', '.') }})</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                <p class="text-xs text-gray-500 dark:text-gray-400">Sisa</p>
                <p class="text-lg font-bold {{ $cashAdvance->remaining_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">Rp {{ number_format($cashAdvance->remaining_amount, 0, ',', '.') }}</p>
            </div>
        </div>

        @if($cashAdvance->purpose)
        <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            <span class="font-medium text-gray-700 dark:text-gray-300">Keterangan:</span> {{ $cashAdvance->purpose }}
        </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Transaksi</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Tanggal</th>
                        <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Keterangan</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Debit</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Kredit</th>
                        <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $tx['date'] instanceof \Carbon\Carbon ? $tx['date']->format('d/m/Y') : \Carbon\Carbon::parse($tx['date'])->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-gray-900 dark:text-white">{{ $tx['description'] }}</td>
                        <td class="py-3 px-4 text-right text-red-600 dark:text-red-400 font-medium">{{ $tx['debit'] > 0 ? 'Rp ' . number_format($tx['debit'], 0, ',', '.') : '-' }}</td>
                        <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-medium">{{ $tx['credit'] > 0 ? 'Rp ' . number_format($tx['credit'], 0, ',', '.') : '-' }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($tx['balance'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('admin.cash-advances.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Kembali</a>
    </div>
</div>
@endsection
