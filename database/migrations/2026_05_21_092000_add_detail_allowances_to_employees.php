<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('allowance_absensi', 15, 2)->default(0)->after('allowance_type');
            $table->decimal('allowance_transport', 15, 2)->default(0)->after('allowance_absensi');
            $table->decimal('allowance_jabatan', 15, 2)->default(0)->after('allowance_transport');
            $table->decimal('allowance_insentif', 15, 2)->default(0)->after('allowance_jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['allowance_absensi', 'allowance_transport', 'allowance_jabatan', 'allowance_insentif']);
        });
    }
};