<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_request', function (Blueprint $table) {
            $table->integer('rut');
            $table->unsignedBigInteger('id_request');

            $table->foreign('rut')->references('rut')->on('users');
            $table->foreign('id_request')->references('id')->on('request');

            $table->timestamps();
            $table->primary(['rut', 'id_request']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_request');
    }
}
