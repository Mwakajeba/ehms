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
        if (Schema::hasTable('student_transcripts')) {
            return;
        }
        
        Schema::create('student_transcripts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->decimal('gpa', 3, 2)->nullable();
            $table->decimal('cgpa', 3, 2)->nullable();
            $table->enum('status', ['good standing', 'probation', 'discontinued'])->default('good standing');
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Foreign keys commented out temporarily - tables may not exist yet
            // $table->foreign('student_id')->references('id')->on('college_students')->onDelete('cascade');
            // $table->foreign('academic_year_id')->references('id')->on('college_academic_years')->onDelete('cascade');
            // $table->foreign('semester_id')->references('id')->on('college_semesters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_transcripts');
    }
};
