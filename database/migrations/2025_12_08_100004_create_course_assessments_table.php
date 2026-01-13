<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('assessment_type_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('weight_percentage');
            $table->integer('max_marks');
            $table->date('assessment_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('assessment_type_id')->references('id')->on('assessment_types')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('college_academic_years')->onDelete('cascade');
            $table->foreign('semester_id')->references('id')->on('college_semesters')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('hr_employees')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_assessments');
    }
};
