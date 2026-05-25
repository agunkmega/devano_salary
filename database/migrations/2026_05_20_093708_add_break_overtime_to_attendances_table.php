<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->datetime('break_out')->nullable()->after('clock_out');
            $table->datetime('break_in')->nullable()->after('break_out');
            $table->datetime('overtime_in')->nullable()->after('break_in');
            $table->datetime('overtime_out')->nullable()->after('overtime_in');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['break_out', 'break_in', 'overtime_in', 'overtime_out']);
        });
    }
};
