<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use Illuminate\Http\Request;

class CashAdvanceController extends Controller
{
    public function create()
    {
        return view('portal.cash-advance.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'installments' => 'required|integer|min:1|max:12',
            'type' => 'required|in:tunai,non_tunai',
            'description' => 'nullable|string|max:500',
        ]);

        $amount = (float) $request->amount;
        $installments = (int) $request->installments;
        $installmentAmount = round($amount / $installments);

        CashAdvance::create([
            'employee_id' => session('portal_employee_id'),
            'amount' => $amount,
            'installment_count' => $installments,
            'installment_amount' => $installmentAmount,
            'remaining_amount' => $amount,
            'type' => $request->type,
            'purpose' => $request->description,
            'status' => 'pending',
        ]);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Pengajuan kasbon berhasil dikirim.');
    }
}
