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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            
            // Assignment & Student
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('stream_id')->nullable()->constrained('streams')->onDelete('set null');
            
            // Submission Details
            $table->enum('submission_type', ['written', 'online_upload', 'photo_upload'])->default('written');
            $table->text('submission_content')->nullable(); // For written submissions
            $table->datetime('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->boolean('is_resubmission')->default(false);
            
            // Status
            $table->enum('status', ['not_started', 'in_progress', 'submitted', 'late', 'marked', 'returned'])->default('not_started');
            
            // Marking & Assessment
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade')->nullable(); // A, B, C, D, E
            $table->text('teacher_comments')->nullable();
            $table->text('corrections')->nullable();
            $table->string('voice_feedback_path')->nullable();
            $table->text('improvement_suggestions')->nullable();
            $table->datetime('marked_at')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Extended Due Date (if individual extension granted)
            $table->date('extended_due_date')->nullable();
            $table->time('extended_due_time')->nullable();
            
            // Company & Branch
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['assignment_id', 'student_id']);
            $table->index(['student_id', 'status']);
            $table->index(['status', 'submitted_at']);
            $table->unique(['assignment_id', 'student_id', 'attempt_number'], 'unique_assignment_student_attempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
