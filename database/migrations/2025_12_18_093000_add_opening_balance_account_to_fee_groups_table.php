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
        Schema::table('fee_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('opening_balance_account_id')->nullable()->after('discount_account_id');
            $table->foreign('opening_balance_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_groups', function (Blueprint $table) {
            $table->dropForeign(['opening_balance_account_id']);
            $table->dropColumn('opening_balance_account_id');
        });
    }
};

