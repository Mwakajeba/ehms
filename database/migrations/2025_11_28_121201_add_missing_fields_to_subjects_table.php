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
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('short_name')->nullable()->after('name');
            $table->enum('subject_type', ['theory', 'practical'])->default('theory')->after('code');
            $table->integer('sort_order')->default(0)->after('subject_type');
            $table->string('type')->nullable()->after('sort_order');
            $table->decimal('credit_hours', 5, 2)->default(1)->after('type');
            $table->integer('theory_hours')->default(0)->after('credit_hours');
            $table->integer('practical_hours')->default(0)->after('theory_hours');
            $table->decimal('passing_marks', 5, 2)->default(40.00)->after('practical_hours');
            $table->decimal('total_marks', 5, 2)->default(100.00)->after('passing_marks');
            $table->boolean('is_active')->default(true)->after('total_marks');
            $table->foreignId('subject_group_id')->nullable()->constrained('subject_groups')->onDelete('set null')->after('is_active');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade')->after('subject_group_id');
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
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['subject_group_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'short_name',
                'subject_type',
                'sort_order',
                'type',
                'credit_hours',
                'theory_hours',
                'practical_hours',
                'passing_marks',
                'total_marks',
                'is_active',
                'subject_group_id',
                'company_id',
                'branch_id',
                'created_by'
            ]);
        });
    }
};
