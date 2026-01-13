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
        // Check if column already exists
        if (!Schema::hasColumn('exam_schedules', 'level')) {
        Schema::table('exam_schedules', function (Blueprint $table) {
                // Check if course_id exists to determine where to place the column
                if (Schema::hasColumn('exam_schedules', 'course_id')) {
                    $table->unsignedTinyInteger('level')
                        ->nullable()
                        ->after('course_id')
                        ->comment('Level/Year of study (1-6)');
                } elseif (Schema::hasColumn('exam_schedules', 'academic_year_id')) {
                    // If course_id doesn't exist, add after academic_year_id
                    $table->unsignedTinyInteger('level')
                        ->nullable()
                        ->after('academic_year_id')
                        ->comment('Level/Year of study (1-6)');
                } else {
                    // Fallback: add at the end
                    $table->unsignedTinyInteger('level')
                        ->nullable()
                        ->comment('Level/Year of study (1-6)');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
