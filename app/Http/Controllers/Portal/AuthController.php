<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        if (session()->has('portal_employee_id')) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'password' => 'required|string',
        ]);

        $employee = Employee::where('is_active', true)
            ->where(function ($q) use ($request) {
                $q->where('nik', $request->identity)
                  ->orWhere('phone', $request->identity);
            })
            ->first();

        if (!$employee || !$employee->birth_date) {
            return back()->withErrors(['identity' => 'NIK/No. Hape atau tanggal lahir salah'])->withInput();
        }

        $birthDate = Carbon::parse($employee->birth_date)->format('Y-m-d');
        if ($request->password !== $birthDate) {
            return back()->withErrors(['identity' => 'NIK/No. Hape atau tanggal lahir salah'])->withInput();
        }

        session(['portal_employee_id' => $employee->id]);

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout()
    {
        session()->forget('portal_employee_id');
        return redirect()->route('portal.login');
    }
}
