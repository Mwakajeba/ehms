<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cumulative_gpa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique();
            $table->unsignedBigInteger('program_id');
            
            // Cumulative Statistics
            $table->integer('total_credits_attempted')->default(0);
            $table->integer('total_credits_earned')->default(0);
            $table->decimal('total_quality_points', 10, 2)->default(0);
            $table->decimal('cgpa', 4, 2)->default(0);
            
            // Program Progress
            $table->integer('total_courses_passed')->default(0);
            $table->integer('total_courses_failed')->default(0);
            $table->integer('semesters_completed')->default(0);
            
            // Classification
            $table->string('class_of_award', 50)->nullable();
            $table->string('academic_standing', 30)->nullable();
            
            // Current Semester
            $table->unsignedBigInteger('current_academic_year_id')->nullable();
            $table->unsignedBigInteger('current_semester_id')->nullable();
            $table->string('current_level', 20)->nullable();
            
            // Status
            $table->enum('program_status', ['active', 'probation', 'suspended', 'withdrawn', 'graduated'])->default('active');
            $table->date('last_calculated_date')->nullable();
            $table->text('remarks')->nullable();
            
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('college_students')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('college_programs')->onDelete('cascade');
            $table->foreign('current_academic_year_id')->references('id')->on('college_academic_years')->onDelete('set null');
            $table->foreign('current_semester_id')->references('id')->on('college_semesters')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cumulative_gpa');
    }
};
