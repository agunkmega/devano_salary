<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('national_holidays', function (Blueprint $table) {
            $table->string('religion')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('national_holidays', function (Blueprint $table) {
            $table->dropColumn('religion');
        });
    }
};
