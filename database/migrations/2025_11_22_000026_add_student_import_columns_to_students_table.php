<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->date('admission_date')->nullable()->after('date_of_birth');
            $table->string('boarding_type')->nullable()->after('address');
            $table->string('has_transport')->nullable()->after('boarding_type');
            $table->unsignedBigInteger('class_id')->nullable()->after('has_transport');
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('class_id');
            $table->unsignedBigInteger('bus_stop_id')->nullable()->after('academic_year_id');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
            $table->foreign('bus_stop_id')->references('id')->on('bus_stops')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['bus_stop_id']);
            $table->dropColumn(['admission_date', 'boarding_type', 'has_transport', 'class_id', 'academic_year_id', 'bus_stop_id']);
        });
    }
};
