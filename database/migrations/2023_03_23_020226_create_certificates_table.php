<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id('id_certificate');
            $table->unsignedBigInteger('id_simulation');
            $table->unsignedBigInteger('id_status');
            $table->timestamps();

            $table->foreign('id_simulation')->references('id_simulation')->on('simulations')->onUpdate('cascade');
            $table->foreign('id_status')->references('id_status')->on('certificate_status')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificates');
    }
}
