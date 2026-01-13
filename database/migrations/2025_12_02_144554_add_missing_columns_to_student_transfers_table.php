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
        Schema::table('student_transfers', function (Blueprint $table) {
            $table->enum('transfer_type', ['transfer_out', 'transfer_in', 're_admission'])->after('student_id');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->after('transfer_type');
            $table->string('previous_school')->nullable()->after('from_school');
            $table->string('new_school')->nullable()->after('to_school');
            $table->string('transfer_certificate_number')->nullable()->after('new_school');
            $table->decimal('outstanding_fees', 15, 2)->default(0)->after('transfer_certificate_number');
            $table->text('academic_records')->nullable()->after('outstanding_fees');
            $table->text('notes')->nullable()->after('academic_records');
            $table->unsignedBigInteger('processed_by')->nullable()->after('notes');
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('processed_by');

            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_transfers', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn([
                'transfer_type', 'status', 'previous_school', 'new_school',
                'transfer_certificate_number', 'outstanding_fees', 'academic_records',
                'notes', 'processed_by', 'academic_year_id'
            ]);
        });
    }
};
