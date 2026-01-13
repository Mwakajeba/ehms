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
        Schema::create('school_exam_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_class_assignment_id');
            $table->unsignedBigInteger('student_id');
            $table->enum('status', ['registered', 'exempted', 'absent', 'attended'])->default('registered');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('exam_class_assignment_id')->references('id')->on('exam_class_assignments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['exam_class_assignment_id', 'student_id'], 'unique_exam_student_registration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_exam_registrations');
    }
};
