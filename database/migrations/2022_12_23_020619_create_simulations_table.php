<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimulationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('id_status');
            $table->integer('calc_secure_comp');
            $table->integer('calc_cobert_prevention');
            $table->date('expiration_date');
            $table->integer('amount_payable');
            $table->integer('percent_prevition');
            $table->integer('percent_sec_comp');
            $table->float('sub_total');
            $table->unsignedBigInteger('id_motive');
            $table->integer('desc_corporation');
            $table->integer('sub_total_aporte');
            $table->integer('percent');
            $table->timestamps();

            $table->foreign('id_request')->references('id')->on('solicitudes')->onUpdate('cascade');
            $table->foreign('id_status')->references('id')->on('status')->onUpdate('cascade');
            $table->foreign('id_motive')->references('id_motive')->on('cancel_motives')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simulations');
    }
}
