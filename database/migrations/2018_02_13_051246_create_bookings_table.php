<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('passenger_id')->index();
            $table->date('date')->index();
            $table->unsignedInteger('puloc_id');
            $table->time('putime');
            $table->unsignedInteger('doloc_id');
            $table->time('dotime');
            $table->unsignedSmallInteger('price');
            $table->unsignedSmallInteger('vehicle');
            $table->unsignedSmallInteger('driver');
            $table->string('puloc_type', 10);
            $table->string('doloc_type', 10);
            $table->string('journal', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
