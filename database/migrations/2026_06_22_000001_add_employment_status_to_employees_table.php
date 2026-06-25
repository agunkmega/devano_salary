<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employees', 'employment_status')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('employment_status', 30)->default('permanent')->after('employee_type');
                $table->date('contract_end_date')->nullable()->after('employment_status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['employment_status', 'contract_end_date']);
        });
    }
};
