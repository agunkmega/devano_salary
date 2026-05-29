<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
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

        $employee = Employee::find(session('portal_employee_id'));
        $admins = User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_HR])->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Kasbon Baru',
                'message' => $employee?->full_name . ' mengajukan kasbon Rp ' . number_format($amount, 0, ',', '.'),
                'type' => 'cash_advance',
                'url' => route('admin.cash-advances.index', ['status' => 'pending']),
                'icon' => 'cash',
            ]);
        }

        return redirect()->route('portal.dashboard')
            ->with('success', 'Pengajuan kasbon berhasil dikirim.');
    }
}
