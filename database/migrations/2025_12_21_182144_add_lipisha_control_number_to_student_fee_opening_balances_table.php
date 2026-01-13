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
        Schema::table('student_fee_opening_balances', function (Blueprint $table) {
            $table->string('lipisha_control_number', 100)->nullable()->after('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fee_opening_balances', function (Blueprint $table) {
            $table->dropColumn('lipisha_control_number');
        });
    }
};
