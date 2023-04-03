<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXmuralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xmurals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('activity', 25);
            $table->string('venue', 30)->index();
            $table->string('unit', 30);
            $table->string('street', 40);
            $table->string('suburb', 25);
            $table->string('city', 25);
            $table->string('view', 3);
            $table->string('geo', 25);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xmurals');
    }
}
