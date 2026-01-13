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
        Schema::table('college_programs', function (Blueprint $table) {
            $table->enum('level', ['certificate', 'diploma', 'bachelor', 'master', 'phd'])->after('duration_years');
            $table->text('objectives')->nullable()->after('description');
            $table->text('requirements')->nullable()->after('objectives');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('college_programs', function (Blueprint $table) {
            $table->dropColumn(['level', 'objectives', 'requirements']);
        });
    }
};
