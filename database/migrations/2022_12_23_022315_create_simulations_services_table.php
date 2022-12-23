<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimulationsServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simulations_services', function (Blueprint $table) {
            $table->unsignedBigInteger('id_simulation');
            $table->unsignedBigInteger('id_service');
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('id_simulation')->references('id')->on('simulations')->onUpdate('cascade');
            $table->foreign('id_service')->references('id')->on('services')->onUpdate('cascade');

            $table->primary(['id_simulation', 'id_service', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simulations_services');
    }
}
