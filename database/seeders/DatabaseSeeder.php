<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            ShiftSeeder::class,
            LeaveTypeSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}
