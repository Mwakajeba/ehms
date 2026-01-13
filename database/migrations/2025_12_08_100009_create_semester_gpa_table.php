<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_gpa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            
            // GPA Calculation
            $table->integer('total_credits_attempted')->default(0);
            $table->integer('total_credits_earned')->default(0);
            $table->decimal('total_quality_points', 10, 2)->default(0);
            $table->decimal('semester_gpa', 4, 2)->default(0);
            
            // Summary
            $table->integer('courses_passed')->default(0);
            $table->integer('courses_failed')->default(0);
            $table->integer('total_courses')->default(0);
            
            // Status
            $table->enum('status', ['in_progress', 'completed', 'published'])->default('in_progress');
            $table->unsignedBigInteger('published_by')->nullable();
            $table->date('published_date')->nullable();
            $table->text('remarks')->nullable();
            
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('college_students')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('college_programs')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('college_academic_years')->onDelete('cascade');
            $table->foreign('semester_id')->references('id')->on('college_semesters')->onDelete('cascade');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unique(['student_id', 'academic_year_id', 'semester_id'], 'unique_semester_gpa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_gpa');
    }
};
