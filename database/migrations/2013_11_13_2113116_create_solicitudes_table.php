<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitudesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_prevention_type');
            $table->string('corporation');
            $table->string('secure');
            $table->string('prevention_doc');
            $table->unsignedBigInteger('id_ubication');
            $table->integer('number_way');
            $table->unsignedBigInteger('id_type_property');
            $table->integer('property_number');

            $table->timestamps();

            $table->foreign('id_type_property')->references('id')->on('property_types')->onUpdate('cascade');
            $table->foreign('id_ubication')->references('id')->on('ubications')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudes');
    }
}
