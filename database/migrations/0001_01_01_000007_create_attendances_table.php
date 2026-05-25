<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->datetime('clock_in')->nullable();
            $table->datetime('clock_out')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'cuti', 'alpha', 'libur'])->default('hadir');
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->string('photo_in')->nullable();
            $table->string('photo_out')->nullable();
            $table->string('location_in')->nullable();
            $table->string('location_out')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->text('manual_reason')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
