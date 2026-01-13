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
        Schema::create('exam_invigilations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_schedule_paper_id');
            $table->unsignedBigInteger('invigilator_id'); // employee_id
            $table->enum('role', ['chief_invigilator', 'invigilator', 'assistant'])->default('invigilator');
            $table->boolean('is_subject_teacher')->default(false); // Should be false per constraint
            $table->time('assigned_start_time');
            $table->time('assigned_end_time');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('exam_schedule_paper_id')->references('id')->on('exam_schedule_papers')->onDelete('cascade');
            $table->foreign('invigilator_id')->references('id')->on('hr_employees')->onDelete('cascade');

            $table->index(['invigilator_id', 'assigned_start_time', 'assigned_end_time'], 'invigilations_invigilator_time_idx');
            $table->unique(['exam_schedule_paper_id', 'invigilator_id'], 'invigilations_paper_invigilator_unique'); // One invigilator per paper
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_invigilations');
    }
};

