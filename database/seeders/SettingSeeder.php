<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => 'PT. Contoh Perusahaan', 'group' => 'company', 'description' => 'Company name'],
            ['key' => 'company_address', 'value' => 'Jl. Contoh No. 123', 'group' => 'company', 'description' => 'Company address'],
            ['key' => 'company_phone', 'value' => '021-1234567', 'group' => 'company', 'description' => 'Company phone number'],
            ['key' => 'company_email', 'value' => 'info@contoh.com', 'group' => 'company', 'description' => 'Company email'],
            ['key' => 'late_penalty_per_minute', 'value' => '2000', 'group' => 'attendance', 'description' => 'Late penalty per minute'],
            ['key' => 'late_tolerance_minutes', 'value' => '15', 'group' => 'attendance', 'description' => 'Late tolerance in minutes'],
            ['key' => 'overtime_rate_per_hour', 'value' => '25000', 'group' => 'attendance', 'description' => 'Overtime rate per hour'],
            ['key' => 'bpjs_rate', 'value' => '2', 'group' => 'payroll', 'description' => 'BPJS contribution percentage'],
            ['key' => 'tax_threshold', 'value' => '4500000', 'group' => 'payroll', 'description' => 'Tax threshold amount'],
            ['key' => 'tax_rate', 'value' => '5', 'group' => 'payroll', 'description' => 'Tax percentage rate'],
            ['key' => 'max_cash_advance', 'value' => '5000000', 'group' => 'payroll', 'description' => 'Maximum cash advance amount'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
