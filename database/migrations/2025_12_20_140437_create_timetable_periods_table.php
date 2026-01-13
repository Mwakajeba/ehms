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
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->onDelete('cascade');
            $table->string('day_of_week'); // Monday, Tuesday, etc.
            $table->integer('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes')->default(40);
            $table->enum('period_type', ['regular', 'break', 'assembly', 'games', 'lunch'])->default('regular');
            $table->string('period_name')->nullable(); // e.g., "Morning Break", "Lunch"
            $table->boolean('is_break')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['timetable_id', 'day_of_week', 'period_number']);
            $table->index(['timetable_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_periods');
    }
};
