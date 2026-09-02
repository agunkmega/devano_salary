<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_id')->nullable()->index();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('start_time')->default('08:00');
            $table->string('end_time')->nullable();
            $table->string('category')->default('task');
            $table->date('date');
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->string('repeat_type')->default('none');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_schedules');
    }
};
