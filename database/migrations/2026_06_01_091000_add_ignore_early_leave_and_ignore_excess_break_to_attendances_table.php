<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('ignore_early_leave')->default(false)->after('ignore_late');
            $table->boolean('ignore_excess_break')->default(false)->after('ignore_early_leave');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['ignore_early_leave', 'ignore_excess_break']);
        });
    }
};
