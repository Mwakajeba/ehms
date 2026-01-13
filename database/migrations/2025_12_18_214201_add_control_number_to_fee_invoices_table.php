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
        Schema::table('fee_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_invoices', 'lipisha_control_number')) {
                $table->string('lipisha_control_number', 100)->nullable()->after('invoice_number');
                $table->index('lipisha_control_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('fee_invoices', 'lipisha_control_number')) {
                $table->dropIndex(['lipisha_control_number']);
                $table->dropColumn('lipisha_control_number');
            }
        });
    }
};
