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
        Schema::create('timetable_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_code')->unique();
            $table->string('room_name');
            $table->text('description')->nullable();
            $table->integer('capacity')->nullable();
            $table->enum('room_type', ['classroom', 'lab', 'library', 'sports', 'music', 'art', 'computer', 'science', 'other'])->default('classroom');
            $table->foreignId('assigned_class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->boolean('is_shared')->default(false);
            $table->json('equipment')->nullable(); // Equipment available in the room
            $table->boolean('is_active')->default(true);
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'branch_id']);
            $table->index('room_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_rooms');
    }
};
