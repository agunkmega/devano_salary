<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
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

        if (!$employee) {
            return back()->withErrors(['identity' => 'NIK/No. Hape atau password salah'])->withInput();
        }

        if ($employee->password) {
            if (!\Illuminate\Support\Facades\Hash::check($request->password, $employee->password)) {
                return back()->withErrors(['identity' => 'NIK/No. Hape atau password salah'])->withInput();
            }
        } else {
            if (!$employee->birth_date) {
                return back()->withErrors(['identity' => 'NIK/No. Hape atau password salah'])->withInput();
            }
            $birthDate = Carbon::parse($employee->birth_date)->format('Y-m-d');
            if ($request->password !== $birthDate) {
                return back()->withErrors(['identity' => 'NIK/No. Hape atau password salah'])->withInput();
            }
        }

        session(['portal_employee_id' => $employee->id]);

        ActivityLog::create([
            'log_type' => 'portal',
            'action' => 'Login',
            'description' => 'Portal login: ' . $employee->full_name . ' (NIK: ' . $employee->nik . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout()
    {
        $name = session('portal_employee_id') ? Employee::find(session('portal_employee_id'))?->full_name : null;

        session()->forget('portal_employee_id');

        ActivityLog::create([
            'log_type' => 'portal',
            'action' => 'Logout',
            'description' => 'Portal logout' . ($name ? ': ' . $name : ''),
        ]);

        return redirect()->route('portal.login');
    }
}
