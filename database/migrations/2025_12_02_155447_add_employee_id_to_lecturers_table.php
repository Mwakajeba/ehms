<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration links lecturers to HR employees.
     * - employee_id: Links to hr_employees table (optional - some lecturers may not be in HR yet)
     * - Keeps lecturer-specific fields: qualification, specialization, employment_rank, title, etc.
     * - Common fields (name, email, phone, etc.) can be retrieved from HR employee when linked
     */
    public function up(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            // Add employee_id to link with HR employees table
            $table->foreignId('employee_id')->nullable()->after('id')->constrained('hr_employees')->onDelete('set null');
            
            // Make personal fields nullable since they can come from HR employee
            // These fields will be used when there's no HR employee linked
            $table->string('staff_no', 100)->nullable()->change();
            $table->string('first_name', 100)->nullable()->change();
            $table->string('last_name', 100)->nullable()->change();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable()->change();
            $table->string('email', 150)->nullable()->change();
            $table->string('phone', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
            
            // Revert nullable changes
            $table->string('staff_no', 100)->nullable(false)->change();
            $table->string('first_name', 100)->nullable(false)->change();
            $table->string('last_name', 100)->nullable(false)->change();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable(false)->change();
            $table->string('email', 150)->nullable(false)->change();
            $table->string('phone', 50)->nullable(false)->change();
        });
    }
};
