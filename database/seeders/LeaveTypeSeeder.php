<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            ['name' => 'Cuti Tahunan', 'code' => 'CT', 'description' => 'Annual leave', 'is_paid' => true, 'max_days_per_year' => 12, 'is_active' => true],
            ['name' => 'Cuti Sakit', 'code' => 'CS', 'description' => 'Sick leave', 'is_paid' => true, 'max_days_per_year' => null, 'is_active' => true],
            ['name' => 'Cuti Melahirkan', 'code' => 'CM', 'description' => 'Maternity leave', 'is_paid' => true, 'max_days_per_year' => 90, 'is_active' => true],
            ['name' => 'Cuti Pernikahan', 'code' => 'CP', 'description' => 'Marriage leave', 'is_paid' => true, 'max_days_per_year' => 3, 'is_active' => true],
            ['name' => 'Izin', 'code' => 'IZ', 'description' => 'Permit leave', 'is_paid' => false, 'max_days_per_year' => null, 'is_active' => true],
            ['name' => 'Izin Tanpa Keterangan', 'code' => 'ITK', 'description' => 'Unpaid leave without explanation', 'is_paid' => false, 'max_days_per_year' => null, 'is_active' => true],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(['code' => $leaveType['code']], $leaveType);
        }
    }
}
