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
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'lipisha_control_number')) {
                $table->dropIndex(['lipisha_control_number']);
                $table->dropColumn('lipisha_control_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'lipisha_control_number')) {
                $table->string('lipisha_control_number', 100)->nullable()->after('discount_value');
                $table->index('lipisha_control_number');
            }
        });
    }
};
