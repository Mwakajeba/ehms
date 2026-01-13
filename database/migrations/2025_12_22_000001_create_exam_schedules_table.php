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
        if (Schema::hasTable('exam_schedules')) {
            return; // Table already exists, skip migration
        }

        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('exam_type_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->string('exam_name'); // e.g., Mid-Term, Terminal, Annual
            $table->enum('term', ['I', 'II', 'III'])->nullable();
            $table->enum('exam_type_category', ['written', 'practical', 'oral'])->default('written');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('exam_days')->nullable(); // ['Monday', 'Tuesday', ...]
            $table->boolean('has_half_day_exams')->default(false);
            $table->integer('min_break_minutes')->default(30); // Minimum break between exams
            $table->enum('status', ['draft', 'scheduled', 'published', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('exam_type_id')->references('id')->on('school_exam_types')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['company_id', 'branch_id']);
            $table->index(['exam_type_id', 'academic_year_id']);
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};

