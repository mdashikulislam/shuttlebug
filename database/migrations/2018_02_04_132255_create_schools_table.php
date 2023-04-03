<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 35);
            $table->string('phone', 12);
            $table->time('dropfrom');
            $table->time('dropby');
            $table->string('unit', 25);
            $table->string('street', 40);
            $table->string('suburb', 25);
            $table->string('city', 25);
            $table->string('geo', 25);
            $table->string('status', 8)->default('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schools');
    }
}
