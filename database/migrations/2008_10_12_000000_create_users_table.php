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
            $table->string('rut')->primary();
            $table->string('names');
            $table->string('lastname');
            $table->date('f_nac');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('landline');
            $table->integer('mobile_phone');
            $table->boolean('employee');
            $table->string('supervisor_rut')->nullable();
            $table->string('otp')->nullable();
            $table->boolean('email_active');
            $table->string('profile_pic')->nullable();
            $table->unsignedBigInteger('id_rol');
            $table->string('api_token')->nullable();

            $table->rememberToken();
            $table->timestamps();

            //$table->foreign('supervisor_rut')->references('rut')->on('users')->onUpdate('CASCADE');
            $table->foreign('id_rol')->references('id')->on('roles')->onUpdate('CASCADE');

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
