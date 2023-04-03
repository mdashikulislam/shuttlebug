<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->date('date');
            $table->string('puloc');
            $table->time('putime');
            $table->string('doloc');
            $table->time('dotime');
            $table->unsignedTinyInteger('passengers');
            $table->unsignedSmallInteger('tripfee');
            $table->unsignedSmallInteger('vehicle');
            $table->unsignedSmallInteger('driver');
            $table->string('pugeo', 25);
            $table->string('dogeo', 25);
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
        Schema::dropIfExists('event_bookings');
    }
}
