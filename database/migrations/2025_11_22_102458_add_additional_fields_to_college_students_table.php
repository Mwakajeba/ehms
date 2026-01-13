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
        Schema::table('college_students', function (Blueprint $table) {
            // Academic Information
            $table->date('admission_date')->nullable()->after('enrollment_year');

            // Personal Information
            $table->string('nationality')->nullable()->after('gender');
            $table->string('id_number')->nullable()->after('nationality');

            // Address Information
            $table->text('permanent_address')->after('id_number');
            $table->text('current_address')->nullable()->after('permanent_address');

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable()->after('current_address');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->enum('emergency_contact_relationship', ['parent', 'guardian', 'sibling', 'spouse', 'relative', 'friend', 'other'])->nullable()->after('emergency_contact_phone');

            // Previous Education
            $table->string('previous_school')->nullable()->after('emergency_contact_relationship');
            $table->string('qualification')->nullable()->after('previous_school');
            $table->string('grade_score')->nullable()->after('qualification');
            $table->integer('completion_year')->nullable()->after('grade_score');

            // Photo
            $table->string('student_photo')->nullable()->after('completion_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('college_students', function (Blueprint $table) {
            $table->dropColumn([
                'admission_date',
                'nationality',
                'id_number',
                'permanent_address',
                'current_address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'previous_school',
                'qualification',
                'grade_score',
                'completion_year',
                'student_photo'
            ]);
        });
    }
};
