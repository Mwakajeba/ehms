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
        Schema::create('lecturer_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecturer_id')->constrained('lecturers')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->enum('type', ['Lecturer', 'Assistant', 'Tutor'])->default('Lecturer');
            $table->integer('hours_per_week')->nullable();
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->date('assigned_date');
            $table->date('unassigned_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Add unique constraint to prevent duplicate assignments
            $table->unique(['lecturer_id', 'course_id', 'academic_year_id', 'semester_id'], 'unique_lecturer_course_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturer_courses');
    }
};
