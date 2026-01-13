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
        if (Schema::hasTable('grade_scales')) {
            return;
        }
        
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 10);
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('exam_type_id');
            $table->date('exam_date');
            $table->decimal('max_marks', 5, 2);
            $table->decimal('pass_marks', 5, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys commented out temporarily - tables may not exist yet
            // $table->foreign('semester_id')->references('id')->on('college_semesters')->onDelete('cascade');
            // $table->foreign('program_id')->references('id')->on('college_programs')->onDelete('cascade');
            // $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            // $table->foreign('exam_type_id')->references('id')->on('exam_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
    }
};
