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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('college_programs')->onDelete('cascade');
            $table->string('code', 20)->unique();
            $table->string('name', 255);
            $table->integer('credit_hours');
            $table->integer('semester');
            $table->enum('level', ['Certificate', 'Diploma', 'Degree', 'Masters', 'PhD'])->default('Degree');
            $table->text('prerequisites')->nullable();
            $table->enum('core_elective', ['Core', 'Elective'])->default('Core');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('program_id');
            $table->index('code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
