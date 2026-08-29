<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->whereNotIn('status', ['draft', 'Draft'])
            ->orderBy('period', 'desc')
            ->paginate(20);

        $data = $payrolls->map(function ($p) {
            return $this->formatPayroll($p);
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $payrolls->currentPage(),
                'last_page' => $payrolls->lastPage(),
                'total' => $payrolls->total(),
            ],
        ]);
    }

    public function show($id, Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $payroll = Payroll::where('employee_id', $employee->id)
            ->whereNotIn('status', ['draft', 'Draft'])
            ->where('id', $id)
            ->first();

        if (!$payroll) {
            return response()->json(['message' => 'Payroll not found.'], 404);
        }

        return response()->json([
            'data' => $this->formatPayroll($payroll),
        ]);
    }

    public function latest(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $payroll = Payroll::where('employee_id', $employee->id)
            ->whereNotIn('status', ['draft', 'Draft'])
            ->orderBy('period', 'desc')
            ->first();

        if (!$payroll) {
            return response()->json(['message' => 'No payroll found.'], 404);
        }

        return response()->json([
            'data' => $this->formatPayroll($payroll),
        ]);
    }

    private function formatPayroll(Payroll $p): array
    {
        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        [$year, $month] = explode('-', $p->period);
        $periodLabel = ($monthNames[$month] ?? $month) . ' ' . $year;

        $allowances = [];
        if ((float) $p->allowance > 0) {
            $allowances[] = ['label' => 'Tunjangan', 'amount' => (int) round((float) $p->allowance)];
        }
        if ((float) $p->bonus > 0) {
            $allowances[] = ['label' => 'Bonus', 'amount' => (int) round((float) $p->bonus)];
        }
        if ((float) $p->overtime_pay > 0) {
            $allowances[] = ['label' => 'Lembur', 'amount' => (int) round((float) $p->overtime_pay)];
        }
        if ((float) $p->uang_makan_lembur > 0) {
            $allowances[] = ['label' => 'Uang Makan Lembur', 'amount' => (int) round((float) $p->uang_makan_lembur)];
        }
        if ((float) $p->uang_makan_harian > 0) {
            $allowances[] = ['label' => 'Uang Makan Harian', 'amount' => (int) round((float) $p->uang_makan_harian)];
        }
        if ((float) ($p->other_additions ?? 0) > 0) {
            $allowances[] = ['label' => 'Penambahan Lain', 'amount' => (int) round((float) $p->other_additions)];
        }

        $deductions = [];
        if ((float) $p->late_penalty > 0) {
            $deductions[] = ['label' => 'Potongan Keterlambatan', 'amount' => (int) round((float) $p->late_penalty)];
        }
        if ((float) ($p->late_penalty_percent ?? 0) > 0) {
            $deductions[] = ['label' => 'Denda Telat (>3x)', 'amount' => (int) round((float) $p->late_penalty_percent)];
        }
        if ((float) $p->absent_penalty > 0) {
            $deductions[] = ['label' => 'Potongan Tidak Hadir', 'amount' => (int) round((float) $p->absent_penalty)];
        }
        if ((float) $p->cash_advance_deduction > 0) {
            $deductions[] = ['label' => 'Cicilan Pinjaman', 'amount' => (int) round((float) $p->cash_advance_deduction)];
        }
        if ((float) $p->bpjs_kesehatan_deduction > 0) {
            $deductions[] = ['label' => 'BPJS Kesehatan', 'amount' => (int) round((float) $p->bpjs_kesehatan_deduction)];
        }
        if ((float) $p->bpjs_ketenagakerjaan_deduction > 0) {
            $deductions[] = ['label' => 'BPJS Ketenagakerjaan', 'amount' => (int) round((float) $p->bpjs_ketenagakerjaan_deduction)];
        }
        if ((float) ($p->iuran_bulanan_deduction ?? 0) > 0) {
            $deductions[] = ['label' => 'Iuran Bulanan', 'amount' => (int) round((float) $p->iuran_bulanan_deduction)];
        }
        if ((float) $p->tax_amount > 0) {
            $deductions[] = ['label' => 'PPH 21', 'amount' => (int) round((float) $p->tax_amount)];
        }
        if ((float) ($p->other_deductions ?? 0) > 0) {
            $deductions[] = ['label' => 'Potongan Lain', 'amount' => (int) round((float) $p->other_deductions)];
        }

        $statusMap = [
            'draft' => 'Draft',
            'approved' => 'Disetujui',
            'paid' => 'Lunas',
        ];

        return [
            'id' => $p->id,
            'period' => $p->period,
            'period_label' => $periodLabel,
            'gross_salary' => (int) round((float) $p->base_salary),
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_salary' => (int) round((float) $p->net_salary),
            'status' => $statusMap[$p->status] ?? $p->status,
            'attendance_days' => $p->attendance_days,
            'paid_leave_days' => $p->paid_leave_days,
            'absent_days' => $p->absent_days,
            'payment_date' => $p->payment_date?->format('Y-m-d'),
            'created_at' => $p->created_at?->format('Y-m-d H:i'),
        ];
    }
}
