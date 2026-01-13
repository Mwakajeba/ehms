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
        Schema::table('subject_teachers', function (Blueprint $table) {
            $table->foreignId('stream_id')->nullable()->constrained('streams')->onDelete('cascade')->after('class_id');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade')->after('academic_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_teachers', function (Blueprint $table) {
            $table->dropForeign(['stream_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['stream_id', 'branch_id']);
        });
    }
};
