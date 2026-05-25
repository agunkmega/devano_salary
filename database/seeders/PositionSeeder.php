<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'HR Manager', 'code' => 'HR-MGR', 'department_code' => 'HRD'],
            ['name' => 'HR Staff', 'code' => 'HR-STF', 'department_code' => 'HRD'],
            ['name' => 'Recruitment Staff', 'code' => 'HR-REC', 'department_code' => 'HRD'],
            ['name' => 'Finance Manager', 'code' => 'FIN-MGR', 'department_code' => 'FIN'],
            ['name' => 'Accountant', 'code' => 'FIN-ACC', 'department_code' => 'FIN'],
            ['name' => 'Finance Staff', 'code' => 'FIN-STF', 'department_code' => 'FIN'],
            ['name' => 'IT Manager', 'code' => 'IT-MGR', 'department_code' => 'IT'],
            ['name' => 'Developer', 'code' => 'IT-DEV', 'department_code' => 'IT'],
            ['name' => 'IT Support', 'code' => 'IT-SUP', 'department_code' => 'IT'],
            ['name' => 'Marketing Manager', 'code' => 'MKT-MGR', 'department_code' => 'MKT'],
            ['name' => 'Marketing Staff', 'code' => 'MKT-STF', 'department_code' => 'MKT'],
            ['name' => 'Content Creator', 'code' => 'MKT-CC', 'department_code' => 'MKT'],
            ['name' => 'Operations Manager', 'code' => 'OPS-MGR', 'department_code' => 'OPS'],
            ['name' => 'Admin Staff', 'code' => 'OPS-ADM', 'department_code' => 'OPS'],
            ['name' => 'Supervisor', 'code' => 'OPS-SPV', 'department_code' => 'OPS'],
        ];

        foreach ($positions as $pos) {
            $department = Department::where('code', $pos['department_code'])->first();
            Position::firstOrCreate(
                ['code' => $pos['code']],
                [
                    'name' => $pos['name'],
                    'department_id' => $department?->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
