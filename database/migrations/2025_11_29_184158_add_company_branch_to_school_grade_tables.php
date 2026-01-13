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
        Schema::table('school_grade_scales', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');

            $table->index(['company_id', 'branch_id']);
        });

        Schema::table('school_grades', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');

            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_grades', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['company_id', 'branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });

        Schema::table('school_grade_scales', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['company_id', 'branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });
    }
};
