<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('late_penalty_percent', 15, 2)->default(0)->after('late_penalty');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('late_penalty_percent');
        });
    }
};
