<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompensatoryDay;
use App\Models\Employee;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class DpBalanceController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $mapBalance = fn(Employee $emp) => [
            'id' => $emp->id,
            'name' => $emp->full_name,
            'granted' => $emp->dp_granted,
            'used' => $emp->dp_used,
            'remaining' => $emp->dp_remaining,
        ];

        $base = Employee::where('is_active', true)->orderBy('full_name');

        $pickerEmployees = $base->get()->map($mapBalance);

        $employees = (clone $base)
            ->when($request->filled('employee'), function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->employee . '%');
            })
            ->get()
            ->map($mapBalance);

        $grants = CompensatoryDay::with(['employee', 'granter'])
            ->when($request->filled('employee'), function ($q) use ($request) {
                $q->whereHas('employee', fn($sub) => $sub->where('full_name', 'like', '%' . $request->employee . '%'));
            })
            ->latest('earned_date')
            ->latest('id')
            ->paginate(20);

        return view('dp.index', compact('employees', 'pickerEmployees', 'grants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'days' => 'required|integer|min:1|max:365',
            'earned_date' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        CompensatoryDay::create([
            'employee_id' => $validated['employee_id'],
            'national_holiday_id' => null,
            'days' => $validated['days'],
            'earned_date' => $validated['earned_date'],
            'status' => 'earned',
            'note' => $validated['note'] ?: 'Input manual oleh HR',
            'granted_by' => auth()->id(),
        ]);

        $employee = Employee::find($validated['employee_id']);
        $this->logActivity('compensatory_day', 'Create', 'Menambah saldo DP ' . $validated['days'] . ' hari untuk ' . $employee?->full_name, 'CompensatoryDay', $employee?->id);

        return redirect()->route('admin.dp.index')
            ->with('success', 'Saldo DP berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'hr'])) {
            return redirect()->route('admin.dp.index')
                ->with('error', 'Anda tidak memiliki izin menghapus saldo DP.');
        }

        $grant = CompensatoryDay::findOrFail($id);
        $name = $grant->employee?->full_name;
        $grant->delete();

        $this->logActivity('compensatory_day', 'Delete', 'Menghapus pemberian DP atas nama ' . $name, 'CompensatoryDay', $id);

        return redirect()->route('admin.dp.index')
            ->with('success', 'Pemberian DP berhasil dihapus.');
    }
}
