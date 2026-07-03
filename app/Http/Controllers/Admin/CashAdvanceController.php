<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use App\Models\CashAdvancePayment;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class CashAdvanceController extends Controller
{
    use LogsActivity;
    public function index()
    {
        $cashAdvances = CashAdvance::with(['employee.user', 'employee.department'])
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('employee_id'), function ($q, $empId) {
                $q->where('employee_id', $empId);
            })
            ->latest()
            ->paginate(20);

        $employees = Employee::where('is_active', true)->get();

        $statusMap = [
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'paid' => 'Lunas',
        ];

        $cashAdvancesData = $cashAdvances->through(function ($ca) use ($statusMap) {
            $name = $ca->employee?->full_name ?? $ca->employee?->user?->name ?? 'Unknown';
            $words = preg_split('/\s+/', trim($name));
            $initials = strtoupper(
                substr($words[0] ?? '', 0, 1) .
                substr($words[1] ?? $words[0] ?? '', 0, 1)
            );

            return [
                'id' => $ca->id,
                'employee' => $name,
                'initials' => $initials,
                'type' => $ca->type === 'nontunai' ? 'Non Tunai' : 'Tunai',
                'amount' => 'Rp ' . number_format($ca->amount, 0, ',', '.'),
                'installments' => $ca->installment_count,
                'remaining' => 'Rp ' . number_format($ca->remaining_amount, 0, ',', '.'),
                'status' => $statusMap[$ca->status] ?? ucfirst($ca->status),
                'submission_date' => $ca->submission_date ? $ca->submission_date->format('d M Y') : '-',
            ];
        });

        return view('cashadvance.index', compact('cashAdvances', 'cashAdvancesData', 'employees'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->get();

        return view('cashadvance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'submission_date' => 'nullable|date',
            'tunai_amount' => 'nullable|numeric|min:0',
            'tunai_installment_count' => 'nullable|integer|min:1|max:24',
            'tunai_purpose' => 'nullable|string',
            'nontunai_amount' => 'nullable|numeric|min:0',
            'nontunai_installment_count' => 'nullable|integer|min:1|max:24',
            'nontunai_purpose' => 'nullable|string',
        ]);

        $tunaiAmount = (float) ($validated['tunai_amount'] ?? 0);
        $nontunaiAmount = (float) ($validated['nontunai_amount'] ?? 0);

        if ($tunaiAmount <= 0 && $nontunaiAmount <= 0) {
            return back()->withErrors(['at_least_one' => 'Minimal salah satu jenis kasbon harus diisi.'])->withInput();
        }

        if ($tunaiAmount > 0 && empty($validated['tunai_installment_count'])) {
            return back()->withErrors(['tunai_installment_count' => 'Jumlah cicilan tunai wajib diisi.'])->withInput();
        }

        if ($nontunaiAmount > 0 && empty($validated['nontunai_installment_count'])) {
            return back()->withErrors(['nontunai_installment_count' => 'Jumlah cicilan non tunai wajib diisi.'])->withInput();
        }

        $submissionDate = $validated['submission_date'] ?? now()->format('Y-m-d');

        if ($tunaiAmount > 0) {
            $tunaiInstallments = (int) $validated['tunai_installment_count'];
            CashAdvance::create([
                'employee_id' => $validated['employee_id'],
                'submission_date' => $submissionDate,
                'type' => 'tunai',
                'amount' => $tunaiAmount,
                'installment_count' => $tunaiInstallments,
                'installment_amount' => $tunaiAmount / $tunaiInstallments,
                'remaining_amount' => $tunaiAmount,
                'purpose' => $validated['tunai_purpose'] ?? '',
                'status' => 'pending',
            ]);
        }

        if ($nontunaiAmount > 0) {
            $nontunaiInstallments = (int) $validated['nontunai_installment_count'];
            CashAdvance::create([
                'employee_id' => $validated['employee_id'],
                'submission_date' => $submissionDate,
                'type' => 'nontunai',
                'amount' => $nontunaiAmount,
                'installment_count' => $nontunaiInstallments,
                'installment_amount' => $nontunaiAmount / $nontunaiInstallments,
                'remaining_amount' => $nontunaiAmount,
                'purpose' => $validated['nontunai_purpose'] ?? '',
                'status' => 'pending',
            ]);
        }

        return redirect()->route('admin.cash-advances.index')
            ->with('success', 'Cash advance request created successfully.');
    }

    public function show(CashAdvance $cashAdvance)
    {
        $cashAdvance->load(['employee.user', 'employee.department', 'employee.position', 'approver', 'payments']);

        return view('cashadvance.show', compact('cashAdvance'));
    }

    public function edit(CashAdvance $cashAdvance)
    {
        $cashAdvance->load('employee');
        return view('cashadvance.edit', compact('cashAdvance'));
    }

    public function update(Request $request, CashAdvance $cashAdvance)
    {
        $validated = $request->validate([
            'submission_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'installment_count' => 'required|integer|min:1|max:24',
            'purpose' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected,paid',
        ]);

        $installmentAmount = (float) $validated['amount'] / (int) $validated['installment_count'];
        $oldAmount = (float) $cashAdvance->amount;
        $diffAmount = (float) $validated['amount'] - $oldAmount;

        $cashAdvance->update([
            'submission_date' => $validated['submission_date'] ?? $cashAdvance->submission_date,
            'amount' => $validated['amount'],
            'installment_count' => $validated['installment_count'],
            'installment_amount' => $installmentAmount,
            'remaining_amount' => max(0, (float) $cashAdvance->remaining_amount + $diffAmount),
            'purpose' => $validated['purpose'] ?? '',
            'status' => $validated['status'],
        ]);

        $this->logActivity('cash_advance', 'Update', 'Mengubah kasbon ' . $cashAdvance->employee?->full_name, 'CashAdvance', $cashAdvance->id);

        return redirect()->route('admin.cash-advances.index')
            ->with('success', 'Cash advance updated successfully.');
    }

    public function destroy(CashAdvance $cashAdvance)
    {
        $employeeName = $cashAdvance->employee?->full_name ?? 'Unknown';
        $cashAdvance->delete();

        $this->logActivity('cash_advance', 'Delete', 'Menghapus kasbon ' . $employeeName, 'CashAdvance');

        return redirect()->route('admin.cash-advances.index')
            ->with('success', 'Cash advance deleted successfully.');
    }

    public function approve($id)
    {
        $cashAdvance = CashAdvance::findOrFail($id);

        $cashAdvance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approval_date' => now(),
        ]);

        $employee = $cashAdvance->employee;
        $this->logActivity('cash_advance', 'Approve', 'Menyetujui kasbon ' . $employee->full_name, 'CashAdvance', $cashAdvance->id);

        if ($employee && $employee->user_id) {
            Notification::create([
                'user_id' => $employee->user_id,
                'title' => 'Kasbon Disetujui',
                'message' => 'Pengajuan kasbon Rp ' . number_format($cashAdvance->amount, 0, ',', '.') . ' telah disetujui',
                'type' => 'cash_advance',
                'icon' => 'check',
            ]);
        }

        return redirect()->route('admin.cash-advances.index')
            ->with('success', 'Cash advance approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $cashAdvance = CashAdvance::findOrFail($id);

        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        $cashAdvance->update([
            'status' => 'rejected',
            'notes' => $validated['notes'],
            'approved_by' => auth()->id(),
            'approval_date' => now(),
        ]);

        $employee = $cashAdvance->employee;
        $this->logActivity('cash_advance', 'Reject', 'Menolak kasbon ' . $employee->full_name, 'CashAdvance', $cashAdvance->id);

        if ($employee && $employee->user_id) {
            Notification::create([
                'user_id' => $employee->user_id,
                'title' => 'Kasbon Ditolak',
                'message' => 'Pengajuan kasbon Rp ' . number_format($cashAdvance->amount, 0, ',', '.') . ' ditolak: ' . $validated['notes'],
                'type' => 'cash_advance',
                'icon' => 'x',
            ]);
        }

        return redirect()->route('admin.cash-advances.index')
            ->with('success', 'Cash advance rejected successfully.');
    }

    public function pay(Request $request, $id)
    {
        $cashAdvance = CashAdvance::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $cashAdvance->remaining_amount,
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $payment = CashAdvancePayment::create([
            'cash_advance_id' => $cashAdvance->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_number' => 'CAP-' . strtoupper(uniqid()),
            'notes' => $validated['notes'] ?? null,
        ]);

        $remaining = $cashAdvance->remaining_amount - $validated['amount'];
        $status = $remaining <= 0 ? 'paid' : 'approved';

        $cashAdvance->update([
            'remaining_amount' => max(0, $remaining),
            'status' => $status,
        ]);

        $employee = $cashAdvance->employee;
        $this->logActivity('cash_advance', 'Pay', 'Membayar kasbon ' . $employee->full_name . ' Rp ' . $validated['amount'], 'CashAdvance', $cashAdvance->id);

        return redirect()->route('admin.cash-advances.show', $cashAdvance->id)
            ->with('success', 'Payment recorded successfully.');
    }
}
