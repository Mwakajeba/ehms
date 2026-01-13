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
        Schema::create('course_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->string('academic_year'); // e.g. "2024/2025"
            $table->string('semester'); // Semester 1 / Semester 2
            $table->datetime('date_assigned');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable(); // Optional notes for changes
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments for same course, year, semester
            $table->unique(['course_id', 'employee_id', 'academic_year', 'semester'], 'unique_course_assignment');
            
            // Indexes for performance
            $table->index(['course_id', 'status']);
            $table->index(['employee_id', 'status']);
            $table->index(['academic_year', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_details');
    }
};
