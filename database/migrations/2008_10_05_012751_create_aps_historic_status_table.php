<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\SerializableClosure\UnsignedSerializableClosure;

class CreateApsHistoricStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aps_historic_status', function (Blueprint $table) {
            $table->unsignedBigInteger('id_aps');
            $table->unsignedBigInteger('id_status');
            $table->timestamps();

            $table->foreign('id_aps')->references('id_aps')->on('update_aps')->onUpdate('cascade');
            $table->foreign('id_status')->references('id_status')->on('aps_status')->onUpdate('cascade');

            $table->primary(['id_aps', 'id_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aps_historic_status');
    }
}
