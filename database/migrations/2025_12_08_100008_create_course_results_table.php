<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_registration_id')->unique();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->integer('attempt_number')->default(1);
            $table->integer('credit_hours');
            
            // Marks Breakdown
            $table->decimal('ca_total', 5, 2)->default(0);
            $table->decimal('exam_total', 5, 2)->default(0);
            $table->decimal('total_marks', 5, 2)->default(0);
            
            // Grading
            $table->string('grade', 5)->nullable();
            $table->decimal('gpa_points', 3, 2)->nullable();
            $table->string('remark', 50)->nullable();
            
            // Additional Info
            $table->string('course_status', 20)->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->boolean('is_retake')->default(false);
            $table->enum('result_status', ['draft', 'published', 'approved'])->default('draft');
            $table->text('remarks')->nullable();
            
            // Approval Workflow
            $table->unsignedBigInteger('published_by')->nullable();
            $table->date('published_date')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('approved_date')->nullable();
            
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('course_registration_id')->references('id')->on('course_registrations')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('college_students')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('college_programs')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('college_academic_years')->onDelete('cascade');
            $table->foreign('semester_id')->references('id')->on('college_semesters')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('hr_employees')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->index(['student_id', 'academic_year_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_results');
    }
};
