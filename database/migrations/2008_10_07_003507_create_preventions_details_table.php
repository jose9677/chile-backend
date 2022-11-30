<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreventionsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventions_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_prevention_type');
            $table->string('prevention_detail');
            $table->timestamps();

            $table->foreign('id_prevention_type')->references('id')->on('preventions_types')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preventions_details');
    }
}
