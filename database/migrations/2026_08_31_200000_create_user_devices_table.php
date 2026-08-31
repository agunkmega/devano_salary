<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_devices')) {
            Schema::create('user_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('device_id')->index();
                $table->string('device_name')->default('Unknown Device');
                $table->string('os_version')->nullable();
                $table->string('device_type')->default('mobile');
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->string('location')->nullable()->default('Indonesia');
                $table->timestamp('last_active_at')->useCurrent();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};