<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_id')->nullable()->index();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['income', 'expense'])->default('expense');
            $table->string('category')->default('Umum');
            $table->dateTime('date');
            $table->text('note')->nullable();
            $table->boolean('is_hr_salary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_transactions');
    }
};
