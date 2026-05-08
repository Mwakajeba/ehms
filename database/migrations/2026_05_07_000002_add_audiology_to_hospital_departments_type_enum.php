<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum needs ALTER to add a new value
        DB::statement("
            ALTER TABLE hospital_departments
            MODIFY COLUMN type ENUM(
                'reception',
                'cashier',
                'triage',
                'doctor',
                'lab',
                'ultrasound',
                'audiology',
                'dental',
                'pharmacy',
                'rch',
                'family_planning',
                'vaccine',
                'injection',
                'observation'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        // Remove audiology from enum (only safe if no rows use it)
        DB::statement("
            ALTER TABLE hospital_departments
            MODIFY COLUMN type ENUM(
                'reception',
                'cashier',
                'triage',
                'doctor',
                'lab',
                'ultrasound',
                'dental',
                'pharmacy',
                'rch',
                'family_planning',
                'vaccine',
                'injection',
                'observation'
            ) NOT NULL
        ");
    }
};

