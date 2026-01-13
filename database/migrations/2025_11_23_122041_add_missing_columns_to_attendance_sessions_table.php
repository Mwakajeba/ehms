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
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('stream_id')->after('class_id');
            $table->unsignedBigInteger('academic_year_id')->after('stream_id');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active')->after('academic_year_id');
            $table->unsignedBigInteger('created_by')->after('status');
            $table->text('notes')->nullable()->after('created_by');

            $table->foreign('stream_id')->references('id')->on('streams')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign(['stream_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['created_by']);
            
            $table->dropColumn(['stream_id', 'academic_year_id', 'status', 'created_by', 'notes']);
        });
    }
};
