<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->string('transcript_number')->unique();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('program_id');
            
            // Transcript Type
            $table->enum('transcript_type', ['semester', 'annual', 'provisional', 'final'])->default('semester');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('semester_id')->nullable();
            
            // Academic Summary
            $table->decimal('cgpa', 4, 2)->default(0);
            $table->integer('total_credits_earned')->default(0);
            $table->string('class_of_award', 50)->nullable();
            $table->string('academic_standing', 30)->nullable();
            
            // Generation Details
            $table->date('generated_date');
            $table->unsignedBigInteger('generated_by');
            $table->string('file_path')->nullable();
            $table->string('file_hash')->nullable();
            
            // Verification
            $table->string('verification_code', 50)->unique();
            $table->boolean('is_verified')->default(false);
            $table->date('verified_date')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'issued', 'revoked'])->default('draft');
            $table->text('remarks')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->date('revoked_date')->nullable();
            
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('college_students')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('college_programs')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('college_academic_years')->onDelete('set null');
            $table->foreign('semester_id')->references('id')->on('college_semesters')->onDelete('set null');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            $table->index(['student_id', 'transcript_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
