<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mobile_app_releases')) {
            Schema::create('mobile_app_releases', function (Blueprint $table) {
                $table->id();
                $table->string('version_name');
                $table->integer('version_code')->default(1);
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_size');
                $table->text('release_notes')->nullable();
                $table->boolean('is_mandatory')->default(false);
                $table->string('platform')->default('android');
                $table->string('uploaded_by')->nullable();
                $table->string('checksum')->nullable();
                $table->unsignedBigInteger('download_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_releases');
    }
};