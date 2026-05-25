<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Shift Pagi',
                'code' => 'PAGI',
                'clock_in_time' => '07:00',
                'clock_out_time' => '16:00',
                'late_tolerance_minutes' => 15,
                'is_night_shift' => false,
                'working_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'is_active' => true,
            ],
            [
                'name' => 'Shift Siang',
                'code' => 'SIANG',
                'clock_in_time' => '13:00',
                'clock_out_time' => '22:00',
                'late_tolerance_minutes' => 15,
                'is_night_shift' => false,
                'working_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'is_active' => true,
            ],
            [
                'name' => 'Shift Malam',
                'code' => 'MALAM',
                'clock_in_time' => '22:00',
                'clock_out_time' => '07:00',
                'late_tolerance_minutes' => 15,
                'is_night_shift' => true,
                'working_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                'is_active' => true,
            ],
            [
                'name' => 'Shift Kantor',
                'code' => 'KANTOR',
                'clock_in_time' => '08:00',
                'clock_out_time' => '17:00',
                'late_tolerance_minutes' => 15,
                'is_night_shift' => false,
                'working_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['code' => $shift['code']], $shift);
        }
    }
}
