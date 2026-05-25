<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'HRD', 'code' => 'HRD', 'description' => 'Human Resources Department', 'is_active' => true],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Finance Department', 'is_active' => true],
            ['name' => 'IT', 'code' => 'IT', 'description' => 'Information Technology Department', 'is_active' => true],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Marketing Department', 'is_active' => true],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Operations Department', 'is_active' => true],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
