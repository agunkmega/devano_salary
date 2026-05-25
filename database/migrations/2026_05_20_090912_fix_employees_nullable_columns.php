<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->enum('gender', ['L', 'P'])->nullable()->change();
            $table->date('join_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->enum('gender', ['L', 'P'])->nullable(false)->change();
            $table->date('join_date')->nullable(false)->change();
        });
    }
};
