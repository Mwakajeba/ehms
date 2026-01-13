<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'admission_number')) {
                $table->string('admission_number')->unique()->after('id');
            }
            if (!Schema::hasColumn('students', 'class_id')) {
                $table->unsignedBigInteger('class_id')->nullable()->after('address');
                $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
            }
            if (!Schema::hasColumn('students', 'stream_id')) {
                $table->unsignedBigInteger('stream_id')->nullable()->after('class_id');
                $table->foreign('stream_id')->references('id')->on('streams')->onDelete('set null');
            }
            if (!Schema::hasColumn('students', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('stream_id');
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
            }
            if (!Schema::hasColumn('students', 'boarding_type')) {
                $table->string('boarding_type')->nullable()->after('academic_year_id');
            }
            if (!Schema::hasColumn('students', 'bus_stop_id')) {
                $table->unsignedBigInteger('bus_stop_id')->nullable()->after('boarding_type');
                $table->foreign('bus_stop_id')->references('id')->on('bus_stops')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'admission_number')) {
                $table->dropColumn('admission_number');
            }
            if (Schema::hasColumn('students', 'class_id')) {
                $table->dropForeign(['class_id']);
                $table->dropColumn('class_id');
            }
            if (Schema::hasColumn('students', 'stream_id')) {
                $table->dropForeign(['stream_id']);
                $table->dropColumn('stream_id');
            }
            if (Schema::hasColumn('students', 'academic_year_id')) {
                $table->dropForeign(['academic_year_id']);
                $table->dropColumn('academic_year_id');
            }
            if (Schema::hasColumn('students', 'boarding_type')) {
                $table->dropColumn('boarding_type');
            }
            if (Schema::hasColumn('students', 'bus_stop_id')) {
                $table->dropForeign(['bus_stop_id']);
                $table->dropColumn('bus_stop_id');
            }
        });
    }
};
