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
        Schema::table('student_attendances', function (Blueprint $table) {
            // Add missing columns
            $table->unsignedBigInteger('attendance_session_id')->after('id');
            $table->enum('status', ['present', 'absent', 'late', 'sick'])->default('present')->after('student_id');
            $table->time('time_in')->nullable()->after('status');
            $table->time('time_out')->nullable()->after('time_in');
            $table->text('notes')->nullable()->after('time_out');

            // Add foreign key
            $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->onDelete('cascade');

            // Drop old columns that are no longer needed
            $table->dropColumn(['attendance_date', 'present', 'remarks']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['attendance_session_id']);
            $table->dropColumn(['attendance_session_id', 'status', 'time_in', 'time_out', 'notes']);

            // Restore old columns
            $table->date('attendance_date')->after('student_id');
            $table->boolean('present')->default(true)->after('attendance_date');
            $table->text('remarks')->nullable()->after('present');
        });
    }
};
