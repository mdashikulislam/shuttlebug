<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTsFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ts_feedback', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->index();
            $table->string('passenger', 25);
            $table->string('data');
            $table->time('duetime');
            $table->time('acttime');
            $table->smallInteger('vehicle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ts_feedback');
    }
}
