<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->date('registration_date');
            $table->enum('status', ['registered', 'dropped', 'completed'])->default('registered');
            $table->integer('attempt_number')->default(1);
            $table->boolean('is_retake')->default(false);
            $table->integer('credit_hours')->default(0);
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('college_students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('college_programs')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('college_academic_years')->onDelete('cascade');
            $table->foreign('semester_id')->references('id')->on('college_semesters')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('hr_employees')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->unique(['student_id', 'course_id', 'academic_year_id', 'semester_id', 'attempt_number'], 'unique_course_registration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_registrations');
    }
};
