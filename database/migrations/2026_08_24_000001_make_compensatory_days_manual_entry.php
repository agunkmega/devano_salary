<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compensatory_days', function (Blueprint $table) {
            $table->dropForeign(['national_holiday_id']);
        });

        Schema::table('compensatory_days', function (Blueprint $table) {
            $table->unsignedBigInteger('national_holiday_id')->nullable()->change();
            $table->integer('days')->default(1)->after('national_holiday_id');
            $table->string('note')->nullable()->after('status');
            $table->foreignId('granted_by')->nullable()->after('note')->constrained('users')->nullOnDelete();
            $table->foreign('national_holiday_id')->references('id')->on('national_holidays')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compensatory_days', function (Blueprint $table) {
            $table->dropForeign(['granted_by']);
            $table->dropForeign(['national_holiday_id']);
            $table->dropColumn(['days', 'note', 'granted_by']);
            $table->unsignedBigInteger('national_holiday_id')->nullable(false)->change();
            $table->foreign('national_holiday_id')->references('id')->on('national_holidays')->cascadeOnDelete();
        });
    }
};
