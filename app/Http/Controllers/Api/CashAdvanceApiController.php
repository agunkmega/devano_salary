<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use App\Models\Employee;
use Illuminate\Http\Request;

class CashAdvanceApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $records = CashAdvance::where('employee_id', $employee->id)
            ->orderBy('id', 'desc')
            ->get();

        $maxLimit = (int) round(($employee->base_salary ?? 5000000) * 0.75);
        $activeLoan = (int) $records->whereIn('status', ['pending', 'approved'])->sum('remaining_amount');
        if ($activeLoan === 0) {
            $activeLoan = (int) $records->whereIn('status', ['pending', 'approved'])->sum('amount');
        }

        $monthlyDeduction = 0;
        foreach ($records->where('status', 'approved') as $adv) {
            $count = max(1, (int) ($adv->installment_count ?? $adv->installments ?? 1));
            $monthlyDeduction += (int) round((float) ($adv->installment_amount ?? ($adv->amount / $count)));
        }

        $statusMap = [
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'paid' => 'Lunas',
        ];

        $data = $records->map(function ($r) use ($statusMap) {
            $tenure = max(1, (int) ($r->installment_count ?? $r->installments ?? 1));
            $monthly = (int) round((float) ($r->installment_amount ?? ($r->amount / $tenure)));

            return [
                'id' => (string) $r->id,
                'amount' => (int) round((float) $r->amount),
                'installments' => $tenure,
                'monthly_amount' => $monthly,
                'reason' => $r->purpose ?? $r->reason ?? 'Pengajuan kasbon',
                'status' => $statusMap[$r->status] ?? ucfirst($r->status),
                'submission_date' => $r->submission_date ? \Carbon\Carbon::parse($r->submission_date)->format('Y-m-d') : $r->created_at?->format('Y-m-d'),
            ];
        });

        return response()->json([
            'data' => $data,
            'summary' => [
                'max_limit' => $maxLimit,
                'active_loan' => $activeLoan,
                'monthly_deduction' => $monthlyDeduction,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100000',
            'installments' => 'required|integer|in:1,3,6,12',
            'reason' => 'required|string',
        ]);

        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $amount = (float) $validated['amount'];
        $installments = (int) $validated['installments'];
        $installmentAmount = (float) ($amount / max(1, $installments));

        $cashAdvance = CashAdvance::create([
            'employee_id' => $employee->id,
            'submission_date' => now()->toDateString(),
            'type' => 'tunai',
            'amount' => $amount,
            'installment_count' => $installments,
            'installment_amount' => $installmentAmount,
            'remaining_amount' => $amount,
            'purpose' => $validated['reason'],
            'status' => 'pending',
        ]);

        \App\Services\NotificationService::notifyCashAdvanceSubmission($cashAdvance);

        $statusMap = [
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'paid' => 'Lunas',
        ];

        return response()->json([
            'message' => 'Pengajuan kasbon berhasil dikirim.',
            'data' => [
                'id' => (string) $cashAdvance->id,
                'amount' => (int) round((float) $cashAdvance->amount),
                'installments' => (int) $cashAdvance->installment_count,
                'monthly_amount' => (int) round((float) $cashAdvance->installment_amount),
                'reason' => $cashAdvance->purpose,
                'status' => $statusMap[$cashAdvance->status] ?? 'Pending',
                'submission_date' => now()->format('Y-m-d'),
            ],
        ], 201);
    }
}
