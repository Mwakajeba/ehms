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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'lipisha_customer_id')) {
                $table->string('lipisha_customer_id', 100)->nullable()->after('lipisha_control_number');
                $table->index('lipisha_customer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'lipisha_customer_id')) {
                $table->dropIndex(['lipisha_customer_id']);
                $table->dropColumn('lipisha_customer_id');
            }
        });
    }
};
