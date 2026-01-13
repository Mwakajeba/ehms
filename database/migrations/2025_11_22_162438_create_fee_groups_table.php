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
        Schema::create('fee_groups', function (Blueprint $table) {
            $table->id();
            $table->string('fee_code', 50)->unique();
            $table->string('name');
            $table->unsignedBigInteger('receivable_account_id')->nullable();
            $table->unsignedBigInteger('income_account_id')->nullable();
            $table->unsignedBigInteger('transport_income_account_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('receivable_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('income_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('transport_income_account_id')->references('id')->on('chart_accounts')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_groups');
    }
};
