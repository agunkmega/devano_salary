<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('bpjs_ketenagakerjaan_type')->nullable()->after('bpjs_ketenagakerjaan');
            $table->boolean('bpjs_kesehatan_active')->default(false)->after('bpjs_kesehatan');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['bpjs_ketenagakerjaan_type', 'bpjs_kesehatan_active']);
        });
    }
};
