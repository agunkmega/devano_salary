<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('cash_advance_tunai', 15, 2)->default(0)->after('cash_advance_deduction');
            $table->decimal('cash_advance_nontunai', 15, 2)->default(0)->after('cash_advance_tunai');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('cash_advance_tunai');
            $table->dropColumn('cash_advance_nontunai');
        });
    }
};
