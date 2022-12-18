<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitudesStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitudes_status', function (Blueprint $table) {
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('id_status');
            $table->timestamps();

            $table->foreign('id_request')->references('id')->on('solicitudes')->onUpdate('cascade');
            $table->foreign('id_status')->references('id')->on('status')->onUpdate('cascade');

            $table->primary(['id_request', 'id_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudes_status');
    }
}
