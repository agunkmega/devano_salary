<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'category')) {
                $table->string('category')->default('perusahaan')->after('content');
            }
            if (!Schema::hasColumn('announcements', 'snippet')) {
                $table->string('snippet', 500)->nullable()->after('category');
            }
            if (!Schema::hasColumn('announcements', 'image')) {
                $table->string('image')->nullable()->after('snippet');
            }
            if (!Schema::hasColumn('announcements', 'is_important')) {
                $table->boolean('is_important')->default(false)->after('image');
            }
            if (!Schema::hasColumn('announcements', 'publish_date')) {
                $table->dateTime('publish_date')->nullable()->after('is_important');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['category', 'snippet', 'image', 'is_important', 'publish_date']);
        });
    }
};