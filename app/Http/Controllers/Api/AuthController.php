<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'password' => 'required|string',
        ]);

        $employee = Employee::where('is_active', true)
            ->where(function ($q) use ($request) {
                $q->where('nik', $request->identity)
                  ->orWhere('identity_number', $request->identity)
                  ->orWhere('email', $request->identity)
                  ->orWhere('phone', $request->identity)
                  ->orWhereHas('user', function ($uq) use ($request) {
                      $uq->where('email', $request->identity);
                  })
                  ->orWhereRaw(self::phoneNormalizer() . ' = ?', [self::normalizePhone($request->identity)]);
            })
            ->first();

        if (!$employee) {
            throw ValidationException::withMessages([
                'identity' => ['NIP / NIK KTP / Email atau kata sandi salah.'],
            ]);
        }

        $passwordValid = false;
        if ($employee->user && $employee->user->password && Hash::check($request->password, $employee->user->password)) {
            $passwordValid = true;
        } elseif ($employee->password && Hash::check($request->password, $employee->password)) {
            $passwordValid = true;
        } elseif ($employee->birth_date && $request->password === \Carbon\Carbon::parse($employee->birth_date)->format('Y-m-d')) {
            $passwordValid = true;
        }

        if (!$passwordValid) {
            throw ValidationException::withMessages([
                'identity' => ['NIP / NIK KTP / Email atau kata sandi salah.'],
            ]);
        }

        $user = $employee->user;
        if (!$user) {
            $user = User::create([
                'name' => $employee->full_name,
                'email' => $employee->email ?? $employee->nik . '@employee',
                'password' => Hash::make($request->password),
                'role' => 'staff',
                'is_active' => true,
            ]);
            $employee->update(['user_id' => $user->id]);
        }

                $token = $user->createToken('mobile-app', ['employee:read'])->plainTextToken;

        // Record User Device Info
        $deviceId = $request->input('device_id') ?? $request->header('X-Device-Id') ?? md5($request->userAgent() . $request->ip());
        $deviceName = $request->input('device_name') ?? ($request->userAgent() ? (str_contains($request->userAgent(), 'Android') ? 'Android Device' : (str_contains($request->userAgent(), 'iPhone') ? 'iPhone' : 'Web Browser')) : 'Perangkat Mobile');
        $osVersion = $request->input('os_version') ?? 'Android/iOS';
        $deviceType = $request->input('device_type') ?? 'mobile';

        try {
            \App\Models\UserDevice::updateOrCreate(
                ['user_id' => $user->id, 'device_id' => $deviceId],
                [
                    'device_name' => $deviceName,
                    'os_version' => $osVersion,
                    'device_type' => $deviceType,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_active_at' => now(),
                ]
            );
        } catch (\Exception $e) {}

        \App\Models\ActivityLog::create([
            'log_type' => 'api',
            'action' => 'Login',
            'description' => 'API login: ' . $employee->full_name . ' (NIK: ' . $employee->nik . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'employee' => [
                'id' => $employee->id,
                'nik' => $employee->nik,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'department' => $employee->department?->name,
                'position' => $employee->position?->name,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function activate(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'identity_number' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $employee = Employee::where('nik', $request->nik)
            ->where('identity_number', $request->identity_number)
            ->first();

        if (!$employee) {
            throw ValidationException::withMessages([
                'nik' => ['Data karyawan tidak ditemukan. Periksa NIP dan NIK KTP Anda.'],
            ]);
        }

        if ($employee->user_id) {
            throw ValidationException::withMessages([
                'nik' => ['Akun ini sudah diaktivasi. Silakan masuk.'],
            ]);
        }

        $user = User::create([
            'name' => $employee->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff',
            'is_active' => true,
            'phone' => $employee->phone,
        ]);

        $defaultPhoto = "https://ui-avatars.com/api/?name=" . urlencode($employee->full_name) . "&background=1E3A8A&color=fff&size=256";

        $employee->update([
            'user_id' => $user->id,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'photo' => $employee->photo ?: $defaultPhoto,
        ]);

                $token = $user->createToken('mobile-app', ['employee:read'])->plainTextToken;

        // Record User Device Info
        $deviceId = $request->input('device_id') ?? $request->header('X-Device-Id') ?? md5($request->userAgent() . $request->ip());
        $deviceName = $request->input('device_name') ?? ($request->userAgent() ? (str_contains($request->userAgent(), 'Android') ? 'Android Device' : (str_contains($request->userAgent(), 'iPhone') ? 'iPhone' : 'Web Browser')) : 'Perangkat Mobile');
        $osVersion = $request->input('os_version') ?? 'Android/iOS';
        $deviceType = $request->input('device_type') ?? 'mobile';

        try {
            \App\Models\UserDevice::updateOrCreate(
                ['user_id' => $user->id, 'device_id' => $deviceId],
                [
                    'device_name' => $deviceName,
                    'os_version' => $osVersion,
                    'device_type' => $deviceType,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_active_at' => now(),
                ]
            );
        } catch (\Exception $e) {}

        return response()->json([
            'message' => 'Akun berhasil diaktivasi.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'employee' => [
                'id' => $employee->id,
                'nik' => $employee->nik,
                'name' => $employee->full_name,
                'email' => $request->email,
                'phone' => $employee->phone,
            ],
        ]);
    }

        public function profile(Request $request)
    {
        $user = $request->user();
        $employee = Employee::with(['department', 'position', 'shift', 'station'])
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'employee' => $employee ? [
                // 1. Akun & Kepegawaian
                'id' => $employee->id,
                'nik' => $employee->nik,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'photo' => $employee->photo_url,
                'department' => $employee->department?->name,
                'department_id' => $employee->department_id,
                'position' => $employee->position?->name,
                'position_id' => $employee->position_id,
                'position_grade' => $employee->position_grade,
                'shift' => $employee->shift?->name,
                'shift_id' => $employee->shift_id,
                'station' => $employee->station?->name,
                'station_id' => $employee->station_id,
                'join_date' => $employee->join_date?->format('Y-m-d'),
                'masa_kerja' => $employee->masa_kerja,
                'employment_status' => $employee->employment_status,
                'employment_status_label' => $employee->employment_status_label,
                'contract_end_date' => $employee->contract_end_date?->format('Y-m-d'),
                'is_active' => (bool) $employee->is_active,
                'employee_type' => $employee->employee_type ?? 'bulanan',
                'employee_type_label' => $employee->employee_type_label,
                'cuti_eligible' => (bool) $employee->cuti_eligible,
                'late_penalty_active' => (bool) $employee->late_penalty_active,
                'full_salary_no_attendance' => (bool) $employee->full_salary_no_attendance,
                'off_days' => $employee->off_days ?? ['sunday'],
                'off_days_locale' => $employee->off_days_locale,
                'off_days_formatted' => $employee->off_days_formatted,

                // 2. Data Pribadi & Kependudukan
                'identity_number' => $employee->identity_number,
                'gender' => $employee->gender,
                'gender_label' => $employee->gender_label,
                'birth_date' => $employee->birth_date?->format('Y-m-d'),
                'religion' => $employee->religion,
                'address' => $employee->address,

                // 3. Gaji & Tunjangan
                'base_salary' => (float) ($employee->base_salary ?? 0),
                'allowance' => (float) ($employee->allowance ?? 0),
                'allowance_type' => $employee->allowance_type,
                'allowance_jabatan' => (float) ($employee->allowance_jabatan ?? 0),
                'allowance_transport' => (float) ($employee->allowance_transport ?? 0),
                'allowance_absensi' => (float) ($employee->allowance_absensi ?? 0),
                'allowance_insentif' => (float) ($employee->allowance_insentif ?? 0),
                'overtime_pay_per_hour' => (float) ($employee->overtime_pay_per_hour ?? 0),
                'uang_makan_lembur' => (float) ($employee->uang_makan_lembur ?? 0),
                'iuran_wajib_amount' => (float) ($employee->iuran_wajib_amount ?? 0),

                // 4. Rekening Bank & Payroll
                'bank_name' => $employee->bank_name,
                'bank_account' => $employee->bank_account,
                'bank_holder' => $employee->bank_holder,

                // 5. BPJS & Jaminan Sosial
                'bpjs_ketenagakerjaan' => $employee->bpjs_ketenagakerjaan,
                'bpjs_ketenagakerjaan_type' => $employee->bpjs_ketenagakerjaan_type,
                'bpjs_kesehatan' => $employee->bpjs_kesehatan,
                'bpjs_kesehatan_active' => (bool) $employee->bpjs_kesehatan_active,
                'bpjs_kesehatan_tanggungan' => (int) ($employee->bpjs_kesehatan_tanggungan ?? 0),
            ] : null,
        ]);
    }


    /**
     * SQL expression that normalizes a phone column to digits
     * with the leading Indonesian prefix converted to '0'.
     */
    private static function phoneNormalizer(): string
    {
        return "CONCAT('0', TRIM(LEADING '0' FROM REGEXP_REPLACE(REGEXP_REPLACE(phone, '[^0-9]', ''), '^62', '0')))";
    }

    /**
     * Normalize a phone input to digits, converting '62' prefix to '0'.
     */
    private static function normalizePhone(string $value): string
    {
        $digits = preg_replace('/[^0-9]/', '', $value) ?? '';
        if (str_starts_with($digits, '62') && strlen($digits) > 9) {
            return '0' . substr($digits, 2);
        }
        return $digits;
    }
}


