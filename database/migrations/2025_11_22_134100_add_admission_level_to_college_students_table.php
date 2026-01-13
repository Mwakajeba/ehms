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
            $table->enum('admission_level', ['Level1', 'Level2', 'Level3', 'Level4', 'Level5', 'Level6'])->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('college_students', function (Blueprint $table) {
            $table->dropColumn('admission_level');
        });
    }
};
