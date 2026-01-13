<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove personal fields since they come from HR Employee
     */
    public function up(): void
    {
        // For SQLite, we need to drop indexes first
        if (DB::getDriverName() === 'sqlite') {
            // Disable foreign key checks temporarily
            DB::statement('PRAGMA foreign_keys=off');
            
            // Drop indexes if they exist
            try {
                DB::statement('DROP INDEX IF EXISTS lecturers_staff_no_unique');
                DB::statement('DROP INDEX IF EXISTS lecturers_national_id_unique');
                DB::statement('DROP INDEX IF EXISTS lecturers_email_unique');
            } catch (\Exception $e) {
                // Indexes may not exist
            }
        }

        Schema::table('lecturers', function (Blueprint $table) {
            // Remove personal fields - these come from HR Employee
            $columns = ['staff_no', 'first_name', 'middle_name', 'last_name', 'gender', 
                       'date_of_birth', 'marital_status', 'national_id', 'email', 'phone', 'address'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('lecturers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=on');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            // Restore personal fields
            if (!Schema::hasColumn('lecturers', 'staff_no')) {
                $table->string('staff_no', 100)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'first_name')) {
                $table->string('first_name', 100)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'middle_name')) {
                $table->string('middle_name', 100)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'last_name')) {
                $table->string('last_name', 100)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'gender')) {
                $table->string('gender', 10)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'marital_status')) {
                $table->string('marital_status', 20)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'national_id')) {
                $table->string('national_id', 50)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'email')) {
                $table->string('email', 150)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'phone')) {
                $table->string('phone', 50)->nullable();
            }
            if (!Schema::hasColumn('lecturers', 'address')) {
                $table->text('address')->nullable();
            }
        });
    }
};
