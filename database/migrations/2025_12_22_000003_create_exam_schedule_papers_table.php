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
        if (Schema::hasTable('exam_schedule_papers')) {
            return;
        }
        
        Schema::create('exam_schedule_papers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_schedule_id');
            $table->unsignedBigInteger('exam_schedule_session_id');
            $table->unsignedBigInteger('exam_class_assignment_id'); // Links to exam_class_assignments
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('stream_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_name');
            $table->string('subject_code')->nullable();
            $table->decimal('total_marks', 8, 2);
            $table->integer('duration_minutes');
            $table->boolean('is_compulsory')->default(true);
            $table->enum('paper_type', ['theory', 'practical', 'oral'])->default('theory');
            $table->integer('subject_priority')->default(0); // Higher priority = scheduled earlier
            $table->boolean('is_heavy_subject')->default(false); // For constraint: avoid two heavy subjects same day
            $table->time('scheduled_start_time');
            $table->time('scheduled_end_time');
            $table->string('venue')->nullable();
            $table->integer('number_of_students')->default(0);
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('exam_schedule_id')->references('id')->on('exam_schedules')->onDelete('cascade');
            $table->foreign('exam_schedule_session_id')->references('id')->on('exam_schedule_sessions')->onDelete('cascade');
            $table->foreign('exam_class_assignment_id')->references('id')->on('exam_class_assignments')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('stream_id')->references('id')->on('streams')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');

            // Use shorter index names to avoid MySQL's 64 character limit
            $table->index(['exam_schedule_id', 'exam_schedule_session_id'], 'exam_sched_schedsess_idx');
            $table->index(['class_id', 'stream_id'], 'class_stream_idx');
            $table->index(['scheduled_start_time', 'scheduled_end_time'], 'sched_start_end_idx');
            $table->index('subject_priority', 'subject_priority_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedule_papers');
    }
};
