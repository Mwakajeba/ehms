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
        Schema::create('college_course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('college_students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('college_academic_years')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('college_semesters')->onDelete('cascade');
            $table->date('enrolled_date')->nullable();
            $table->enum('status', ['enrolled', 'dropped', 'completed', 'failed'])->default('enrolled');
            $table->decimal('grade', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint to prevent duplicate enrollments
            $table->unique(['student_id', 'course_id', 'academic_year_id', 'semester_id'], 'unique_course_enrollment');
            
            // Indexes for performance
            $table->index(['course_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('college_course_enrollments');
    }
};
