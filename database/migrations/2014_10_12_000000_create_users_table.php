<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->integer('rut')->primary();
            $table->string('names');
            $table->string('lastname');
            $table->date('f_nac');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('landline');
            $table->integer('mobile_phone');
            $table->unsignedBigInteger('id_way');
            $table->string('way_name');
            $table->integer('number_way');
            $table->unsignedBigInteger('id_property');
            $table->string('name_num_property');
            $table->string('otp');

            $table->rememberToken();
            $table->timestamps();

            $table->softDeletes();
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
