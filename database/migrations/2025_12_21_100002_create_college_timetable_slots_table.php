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
        Schema::create('college_timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timetable_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable(); // hr_employees
            $table->string('day_of_week'); // Monday, Tuesday, etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->string('slot_type')->default('lecture'); // lecture, tutorial, practical, lab, seminar
            $table->string('group_name')->nullable(); // For split groups: Group A, Group B
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('timetable_id')->references('id')->on('college_timetables')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('venue_id')->references('id')->on('college_venues')->onDelete('set null');
            $table->foreign('instructor_id')->references('id')->on('hr_employees')->onDelete('set null');

            // Index for quick lookups
            $table->index(['timetable_id', 'day_of_week']);
            $table->index(['venue_id', 'day_of_week', 'start_time', 'end_time'], 'venue_schedule_index');
            $table->index(['instructor_id', 'day_of_week', 'start_time', 'end_time'], 'instructor_schedule_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('college_timetable_slots');
    }
};
