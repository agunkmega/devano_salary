<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'nik' => 'EMP-001',
                'user_email' => 'superadmin@example.com',
                'department_code' => 'IT',
                'position_code' => 'IT-MGR',
                'shift_code' => 'KANTOR',
                'full_name' => 'Super Admin',
                'birth_date' => '1990-01-15',
                'gender' => 'L',
                'phone' => '081234567890',
                'address' => 'Jl. Contoh No. 1, Jakarta',
                'join_date' => '2020-01-01',
                'status' => 'aktif',
                'base_salary' => 15000000,
                'allowance' => 2000000,
                'is_active' => true,
            ],
            [
                'nik' => 'EMP-002',
                'user_email' => 'hr@example.com',
                'department_code' => 'HRD',
                'position_code' => 'HR-MGR',
                'shift_code' => 'KANTOR',
                'full_name' => 'HR User',
                'birth_date' => '1992-05-20',
                'gender' => 'P',
                'phone' => '081234567891',
                'address' => 'Jl. Contoh No. 2, Jakarta',
                'join_date' => '2020-06-01',
                'status' => 'aktif',
                'base_salary' => 12000000,
                'allowance' => 1500000,
                'is_active' => true,
            ],
            [
                'nik' => 'EMP-003',
                'user_email' => 'manager@example.com',
                'department_code' => 'OPS',
                'position_code' => 'OPS-MGR',
                'shift_code' => 'KANTOR',
                'full_name' => 'Manager User',
                'birth_date' => '1988-11-10',
                'gender' => 'L',
                'phone' => '081234567892',
                'address' => 'Jl. Contoh No. 3, Jakarta',
                'join_date' => '2021-01-01',
                'status' => 'aktif',
                'base_salary' => 13000000,
                'allowance' => 1500000,
                'is_active' => true,
            ],
            [
                'nik' => 'EMP-004',
                'user_email' => 'staff@example.com',
                'department_code' => 'IT',
                'position_code' => 'IT-DEV',
                'shift_code' => 'PAGI',
                'full_name' => 'Staff User',
                'birth_date' => '1995-03-25',
                'gender' => 'L',
                'phone' => '081234567893',
                'address' => 'Jl. Contoh No. 4, Jakarta',
                'join_date' => '2022-03-01',
                'status' => 'aktif',
                'base_salary' => 8000000,
                'allowance' => 1000000,
                'is_active' => true,
            ],
            [
                'nik' => 'EMP-005',
                'user_email' => null,
                'department_code' => 'HRD',
                'position_code' => 'HR-STF',
                'shift_code' => 'KANTOR',
                'full_name' => 'Budi Santoso',
                'birth_date' => '1993-07-08',
                'gender' => 'L',
                'phone' => '081234567894',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'join_date' => '2022-07-15',
                'status' => 'aktif',
                'base_salary' => 6500000,
                'allowance' => 500000,
                'is_active' => true,
            ],
            [
                'nik' => 'EMP-006',
                'user_email' => null,
                'department_code' => 'FIN',
                'position_code' => 'FIN-ACC',
                'shift_code' => 'KANTOR',
                'full_name' => 'Siti Rahayu',
                'birth_date' => '1991-12-01',
                'gender' => 'P',
                'phone' => '081234567895',
                'address' => 'Jl. Sudirman No. 5, Jakarta',
                'join_date' => '2021-11-01',
                'status' => 'aktif',
                'base_salary' => 7000000,
                'allowance' => 500000,
                'is_active' => true,
            ],
            [
                'nik' => 'EMP-007',
                'user_email' => null,
                'department_code' => 'MKT',
                'position_code' => 'MKT-STF',
                'shift_code' => 'KANTOR',
                'full_name' => 'Ahmad Fauzi',
                'birth_date' => '1996-09-15',
                'gender' => 'L',
                'phone' => '081234567896',
                'address' => 'Jl. Gatot Subroto No. 8, Jakarta',
                'join_date' => '2023-01-10',
                'status' => 'aktif',
                'base_salary' => 6000000,
                'allowance' => 500000,
                'is_active' => true,
            ],
            [
                'nik' => 'EMP-008',
                'user_email' => null,
                'department_code' => 'OPS',
                'position_code' => 'OPS-ADM',
                'shift_code' => 'PAGI',
                'full_name' => 'Dewi Lestari',
                'birth_date' => '1998-04-20',
                'gender' => 'P',
                'phone' => '081234567897',
                'address' => 'Jl. Thamrin No. 12, Jakarta',
                'join_date' => '2023-06-01',
                'status' => 'aktif',
                'base_salary' => 5000000,
                'allowance' => 300000,
                'is_active' => true,
            ],
        ];

        foreach ($employees as $data) {
            $user = $data['user_email'] ? User::where('email', $data['user_email'])->first() : null;
            $department = Department::where('code', $data['department_code'])->first();
            $position = Position::where('code', $data['position_code'])->first();
            $shift = Shift::where('code', $data['shift_code'])->first();

            Employee::firstOrCreate(
                ['nik' => $data['nik']],
                [
                    'user_id' => $user?->id,
                    'department_id' => $department?->id ?? 1,
                    'position_id' => $position?->id ?? 1,
                    'shift_id' => $shift?->id ?? 1,
                    'full_name' => $data['full_name'],
                    'birth_date' => $data['birth_date'],
                    'gender' => $data['gender'],
                    'phone' => $data['phone'],
                    'address' => $data['address'] ?? null,
                    'join_date' => $data['join_date'],
                    'status' => $data['status'],
                    'base_salary' => $data['base_salary'],
                    'allowance' => $data['allowance'],
                    'is_active' => $data['is_active'],
                ]
            );
        }
    }
}