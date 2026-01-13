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
            $table->unsignedBigInteger('fee_group_id')->nullable()->after('academic_year_id');
            $table->foreign('fee_group_id')->references('id')->on('fee_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fee_opening_balances', function (Blueprint $table) {
            $table->dropForeign(['fee_group_id']);
            $table->dropColumn('fee_group_id');
        });
    }
};

