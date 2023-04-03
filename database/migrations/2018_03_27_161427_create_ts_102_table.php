<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTs102Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ts_102', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->index();
            $table->string('plan', 3);
            $table->tinyInteger('route');
            $table->string('type', 7);
            $table->time('putime');
            $table->time('dotime')->default('000:00:00');
            $table->time('arrive');
            $table->time('depart');
            $table->string('venue', 60);
            $table->string('passengers', 180);
            $table->tinyInteger('age');
            $table->integer('pass_id')->unsigned();
            $table->tinyInteger('legacy')->default(0);
            $table->string('address', 60);
            $table->string('geo', 25);
            $table->string('warning', 35);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ts_102');
    }
}
