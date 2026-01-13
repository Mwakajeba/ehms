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
        Schema::table('school_exam_marks', function (Blueprint $table) {
            $table->unsignedBigInteger('exam_class_assignment_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('marks_obtained', 5, 2)->nullable();
            $table->decimal('max_marks', 5, 2)->default(100);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->foreign('exam_class_assignment_id')->references('id')->on('exam_class_assignments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['exam_class_assignment_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_exam_marks', function (Blueprint $table) {
            $table->dropForeign(['exam_class_assignment_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by']);

            $table->dropColumn([
                'exam_class_assignment_id',
                'student_id',
                'marks_obtained',
                'max_marks',
                'company_id',
                'branch_id',
                'created_by'
            ]);
        });
    }
};
