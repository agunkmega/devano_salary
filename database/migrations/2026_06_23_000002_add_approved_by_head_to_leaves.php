<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leaves', 'approved_by_head')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->foreignId('approved_by_head')->nullable()->constrained('employees')->nullOnDelete()->after('approved_by');
            });
        }
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['approved_by_head']);
            $table->dropColumn('approved_by_head');
        });
    }
};
