<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'cuti_eligible')) {
                $table->boolean('cuti_eligible')->default(true)->after('late_penalty_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'cuti_eligible')) {
                $table->dropColumn('cuti_eligible');
            }
        });
    }
};