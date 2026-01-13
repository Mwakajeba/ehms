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
        // Drop lecturer-related tables
        Schema::dropIfExists('lecturer_courses');
        Schema::dropIfExists('lecturer_programs');
        Schema::dropIfExists('lecturers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables are not restored on rollback
    }
};
