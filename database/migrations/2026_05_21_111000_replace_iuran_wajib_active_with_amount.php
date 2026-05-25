<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('iuran_wajib_active');
            $table->decimal('iuran_wajib_amount', 15, 2)->nullable()->after('bpjs_kesehatan_active');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('iuran_wajib_amount');
            $table->boolean('iuran_wajib_active')->default(false)->after('bpjs_kesehatan_active');
        });
    }
};
