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
        Schema::table('school_grade_scales', function (Blueprint $table) {
            $table->decimal('passed_average_point', 5, 2)->after('max_marks')->default(50.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_grade_scales', function (Blueprint $table) {
            $table->dropColumn('passed_average_point');
        });
    }
};
