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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            
            // Assignment ID (unique identifier)
            $table->string('assignment_id')->unique();
            
            // Basic Information
            $table->string('title');
            $table->enum('type', ['homework', 'classwork', 'project', 'revision_task'])->default('homework');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            
            // Academic Setup
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('term')->nullable(); // Term I, II, III
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('hr_employees')->onDelete('cascade');
            
            // Scheduling
            $table->date('date_assigned');
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->integer('estimated_completion_time')->nullable(); // in minutes
            $table->boolean('is_recurring')->default(false);
            $table->json('recurring_schedule')->nullable(); // For recurring assignments
            
            // Submission Settings
            $table->enum('submission_type', ['written', 'online_upload', 'photo_upload'])->default('written');
            $table->boolean('resubmission_allowed')->default(false);
            $table->integer('max_attempts')->default(1);
            $table->boolean('lock_after_deadline')->default(false);
            
            // Marking & Assessment
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->decimal('passing_marks', 8, 2)->nullable();
            $table->text('rubric')->nullable(); // Marking guide/rubric
            $table->boolean('auto_graded')->default(false);
            
            // Rules & Constraints
            $table->boolean('one_per_subject_per_day')->default(false);
            $table->integer('homework_load_limit_per_day')->nullable();
            $table->boolean('exclude_holidays')->default(true);
            $table->boolean('exclude_weekends')->default(false);
            
            // Status
            $table->enum('status', ['draft', 'published', 'closed', 'archived'])->default('draft');
            $table->boolean('is_active')->default(true);
            
            // Company & Branch
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            
            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'branch_id', 'is_active']);
            $table->index(['academic_year_id', 'subject_id']);
            $table->index(['teacher_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
