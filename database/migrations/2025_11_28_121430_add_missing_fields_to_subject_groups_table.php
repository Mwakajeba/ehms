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
        Schema::table('subject_groups', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->string('category')->nullable()->after('description');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null')->after('category');
            $table->boolean('is_active')->default(true)->after('class_id');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade')->after('is_active');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null')->after('company_id');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade')->after('branch_id');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_groups', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'code',
                'category',
                'class_id',
                'is_active',
                'company_id',
                'branch_id',
                'created_by'
            ]);
        });
    }
};
