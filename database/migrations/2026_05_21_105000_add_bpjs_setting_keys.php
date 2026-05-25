<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'bpjs_ketenagakerjaan_full_rate', 'value' => '3.7', 'group' => 'payroll', 'description' => 'BPJS Ketenagakerjaan full tanggungan (%)'],
            ['key' => 'bpjs_ketenagakerjaan_partial_rate', 'value' => '1', 'group' => 'payroll', 'description' => 'BPJS Ketenagakerjaan hanya kecelakaan & kematian (%)'],
            ['key' => 'bpjs_kesehatan_rate', 'value' => '4', 'group' => 'payroll', 'description' => 'BPJS Kesehatan (%)'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'bpjs_ketenagakerjaan_full_rate',
            'bpjs_ketenagakerjaan_partial_rate',
            'bpjs_kesehatan_rate',
        ])->delete();
    }
};
