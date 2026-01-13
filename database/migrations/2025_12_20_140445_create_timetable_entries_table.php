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
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->onDelete('cascade');
            $table->foreignId('period_id')->constrained('timetable_periods')->onDelete('cascade');
            $table->string('day_of_week'); // Monday, Tuesday, etc.
            $table->integer('period_number');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->foreignId('stream_id')->nullable()->constrained('streams')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('hr_employees')->onDelete('set null');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->boolean('is_double_period')->default(false);
            $table->boolean('is_practical')->default(false);
            $table->enum('subject_type', ['compulsory', 'optional'])->default('compulsory');
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['timetable_id', 'day_of_week', 'period_number']);
            $table->index(['teacher_id', 'day_of_week', 'period_number']);
            $table->index(['room_id', 'day_of_week', 'period_number']);
            $table->index(['class_id', 'stream_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
