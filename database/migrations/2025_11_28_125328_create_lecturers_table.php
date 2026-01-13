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
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained('college_departments')->onDelete('set null');
            $table->string('staff_no', 100)->unique();
            $table->enum('title', ['Mr', 'Ms', 'Dr', 'Prof', 'Eng', 'Other']);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->date('date_of_birth')->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable();
            $table->string('national_id', 50)->nullable()->unique();
            $table->string('email', 150)->unique();
            $table->string('phone', 50);
            $table->text('address')->nullable();
            $table->text('qualification')->nullable();
            $table->text('specialization')->nullable();
            $table->enum('employment_rank', ['Tutor', 'Assistant Lecturer', 'Lecturer', 'Senior Lecturer', 'Associate Professor', 'Professor']);
            $table->enum('employment_type', ['Full-time', 'Part-time', 'Visiting', 'Adjunct']);
            $table->enum('employment_status', ['Active', 'On Leave', 'Suspended', 'Retired', 'Terminated'])->default('Active');
            $table->date('hire_date');
            $table->date('contract_end_date')->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 50)->nullable();
            $table->string('photo', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
