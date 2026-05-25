<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compensatory_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('national_holiday_id')->constrained()->cascadeOnDelete();
            $table->date('earned_date');
            $table->date('used_date')->nullable();
            $table->enum('status', ['earned', 'used', 'expired'])->default('earned');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compensatory_days');
    }
};
