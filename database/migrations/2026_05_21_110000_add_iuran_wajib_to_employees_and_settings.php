<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('iuran_wajib_active')->default(false)->after('bpjs_kesehatan_active');
        });

        DB::table('settings')->updateOrInsert(
            ['key' => 'iuran_wajib_amount'],
            ['value' => '50000', 'group' => 'payroll', 'description' => 'Iuran Wajib (Rp)']
        );
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('iuran_wajib_active');
        });

        DB::table('settings')->where('key', 'iuran_wajib_amount')->delete();
    }
};
