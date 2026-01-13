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
        Schema::table('school_exam_registrations', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->after('student_id');
            $table->unsignedBigInteger('exam_type_id')->after('academic_year_id');

            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('exam_type_id')->references('id')->on('school_exam_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_exam_registrations', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['exam_type_id']);
            $table->dropColumn(['academic_year_id', 'exam_type_id']);
        });
    }
};
