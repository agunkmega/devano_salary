<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leaves', 'submission_date')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->date('submission_date')->nullable()->after('end_date');
            });
        }
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('submission_date');
        });
    }
};
