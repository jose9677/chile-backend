<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_requests', function (Blueprint $table) {
            $table->string('rut');
            $table->unsignedBigInteger('id_request');
            $table->string('employee_rut');

            $table->foreign('rut')->references('rut')->on('users')->onUpdate('cascade');
            $table->foreign('id_request')->references('id')->on('solicitudes')->onUpdate('cascade');
            $table->foreign('employee_rut')->references('rut')->on('users')->onUpdate('cascade');

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
