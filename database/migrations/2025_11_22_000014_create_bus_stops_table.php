<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bus_stops', function (Blueprint $table) {
            $table->id();
            $table->string('stop_name');
            $table->string('stop_code')->nullable();
            $table->text('description')->nullable();
            $table->decimal('fare', 10, 2)->default(0);
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('sequence_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('bus_id')->nullable();
            $table->timestamps();

            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('set null');
        });

        Schema::create('route_bus_stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bus_stop_id');
            $table->unsignedBigInteger('route_id');
            $table->integer('sequence_order')->default(0);
            $table->timestamps();

            $table->foreign('bus_stop_id')->references('id')->on('bus_stops')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('route_bus_stops');
        Schema::dropIfExists('bus_stops');
    }
};
