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
        Schema::create('timetable_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->onDelete('cascade');
            $table->time('school_start_time')->default('08:00:00');
            $table->time('school_end_time')->default('15:00:00');
            $table->integer('period_duration_minutes')->default(40);
            $table->integer('periods_per_day')->default(8);
            $table->time('morning_break_start')->nullable();
            $table->integer('morning_break_duration')->nullable(); // in minutes
            $table->time('lunch_break_start')->nullable();
            $table->integer('lunch_break_duration')->nullable(); // in minutes
            $table->time('assembly_time')->nullable();
            $table->enum('assembly_frequency', ['daily', 'weekly', 'none'])->default('weekly');
            $table->string('assembly_day')->nullable(); // Monday, Friday, etc.
            $table->time('games_time')->nullable();
            $table->string('games_day')->nullable();
            $table->json('school_days')->nullable(); // ['Monday', 'Tuesday', ...]
            $table->json('half_days')->nullable(); // Days that are half days
            $table->json('special_days')->nullable(); // Special days configuration
            $table->integer('max_periods_per_day_teacher')->default(6);
            $table->integer('max_periods_per_week_teacher')->default(30);
            $table->boolean('require_free_period_per_day')->default(true);
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();

            $table->unique('timetable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_settings');
    }
};
