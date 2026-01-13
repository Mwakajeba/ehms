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
        Schema::create('school_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_scale_id');
            $table->string('grade_letter', 5); // A, B+, B, C+, C, D, F
            $table->string('grade_name'); // Excellent, Very Good, Good, Satisfactory, etc.
            $table->decimal('min_marks', 5, 2);
            $table->decimal('max_marks', 5, 2);
            $table->decimal('grade_point', 3, 2)->nullable(); // GPA point
            $table->text('remarks')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('grade_scale_id')->references('id')->on('school_grade_scales')->onDelete('cascade');
            $table->unique(['grade_scale_id', 'grade_letter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_grades');
    }
};
