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
        Schema::table('streams', function (Blueprint $table) {
            // Add company_id if it doesn't exist
            if (!Schema::hasColumn('streams', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('description')->constrained()->onDelete('cascade');
            }
            
            // Add branch_id if it doesn't exist
            if (!Schema::hasColumn('streams', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->onDelete('cascade');
            }
            
            // Add is_active if it doesn't exist
            if (!Schema::hasColumn('streams', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('branch_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            if (Schema::hasColumn('streams', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('streams', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('streams', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
        });
    }
};
