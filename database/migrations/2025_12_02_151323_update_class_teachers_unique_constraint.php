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
        Schema::table('class_teachers', function (Blueprint $table) {
            // Drop foreign key constraints that might reference the unique constraint
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['stream_id']);
            
            // Drop the existing unique constraint
            $table->dropUnique('unique_class_teacher_assignment');
            
            // Add new unique constraint that includes stream_id
            $table->unique(['employee_id', 'class_id', 'stream_id', 'academic_year_id'], 'unique_class_teacher_assignment');
            
            // Recreate foreign key constraints
            $table->foreign('employee_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('stream_id')->references('id')->on('streams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_teachers', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['stream_id']);
            
            // Drop the new unique constraint
            $table->dropUnique('unique_class_teacher_assignment');
            
            // Add back the old unique constraint
            $table->unique(['employee_id', 'class_id', 'academic_year_id'], 'unique_class_teacher_assignment');
            
            // Recreate foreign key constraints
            $table->foreign('employee_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('stream_id')->references('id')->on('streams')->onDelete('cascade');
        });
    }
};
