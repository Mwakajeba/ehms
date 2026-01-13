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
        Schema::create('exam_schedule_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_schedule_id');
            $table->date('session_date');
            $table->string('session_name'); // Morning, Mid-morning, Afternoon
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_half_day')->default(false);
            $table->integer('order')->default(1); // Order within the day
            $table->timestamps();

            $table->foreign('exam_schedule_id')->references('id')->on('exam_schedules')->onDelete('cascade');
            $table->index(['exam_schedule_id', 'session_date']);
            $table->index(['session_date', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedule_sessions');
    }
};