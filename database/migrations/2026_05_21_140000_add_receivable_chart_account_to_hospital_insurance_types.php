<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospital_insurance_types', function (Blueprint $table) {
            $table->foreignId('receivable_chart_account_id')
                ->nullable()
                ->after('sort_order')
                ->constrained('chart_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hospital_insurance_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receivable_chart_account_id');
        });
    }
};
