<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 15);
            $table->string('last_name', 25)->index();
            $table->string('email')->unique();
            $table->string('phone', 12);
            $table->string('mobile', 12);
            $table->string('role', 12)->index();
            $table->string('relation', 15);
            $table->string('status', 8)->default('active');
            $table->string('unit', 25);
            $table->string('street', 40);
            $table->string('suburb', 25);
            $table->string('city', 25);
            $table->string('geo', 25);
            $table->date('joindate');
            $table->string('inv_email');
            $table->string('inv_name', 35);
            $table->string('inv_adrs', 60);
            $table->string('password');
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
