<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChildrensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('children', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('school_id')->index();
            $table->string('first_name', 15);
            $table->string('last_name', 25)->index();
            $table->date('dob');
            $table->string('gender', 5);
            $table->string('phone', 12);
            $table->string('friend', 6);
            $table->string('medical', 250);
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
        Schema::dropIfExists('children');
    }
}
