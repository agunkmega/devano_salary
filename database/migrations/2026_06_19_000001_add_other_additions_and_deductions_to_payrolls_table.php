<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('other_additions', 15, 2)->default(0)->after('uang_makan_harian');
            $table->decimal('other_deductions', 15, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['other_additions', 'other_deductions']);
        });
    }
};
