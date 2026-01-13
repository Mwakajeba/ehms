<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing subject_group_id relationships to the pivot table
        $subjects = DB::table('subjects')->whereNotNull('subject_group_id')->get();

        foreach ($subjects as $subject) {
            DB::table('subject_subject_group')->insert([
                'subject_id' => $subject->id,
                'subject_group_id' => $subject->subject_group_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all records from the pivot table
        DB::table('subject_subject_group')->delete();
    }
};
