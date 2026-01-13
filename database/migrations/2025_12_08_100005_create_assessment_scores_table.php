<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_registration_id');
            $table->unsignedBigInteger('course_assessment_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->decimal('score', 5, 2);
            $table->integer('max_marks');
            $table->decimal('weighted_score', 5, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->date('submitted_date')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->date('marked_date')->nullable();
            $table->enum('status', ['pending', 'marked', 'published'])->default('pending');
            $table->timestamps();

            $table->foreign('course_registration_id')->references('id')->on('course_registrations')->onDelete('cascade');
            $table->foreign('course_assessment_id')->references('id')->on('course_assessments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('college_students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['student_id', 'course_id']);
            $table->index('course_registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
    }
};
