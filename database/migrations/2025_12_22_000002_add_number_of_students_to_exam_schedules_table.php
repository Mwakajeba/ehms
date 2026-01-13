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
        if (Schema::hasColumn('exam_schedules', 'number_of_students')) {
            return;
        }
        
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->integer('number_of_students')->nullable()->comment('Expected number of students taking the exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropColumn('number_of_students');
        });
    }
};
