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
        Schema::create('college_student_parent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('college_students')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('college_guardians')->onDelete('cascade');
            $table->string('relationship');
            $table->timestamps();

            $table->unique(['student_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('college_student_parent');
    }
};
