<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpdateApsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('update_aps', function (Blueprint $table) {
            $table->id('id_aps');
            $table->integer('cant_records');
            $table->integer('coincidence');
            $table->string('user');
            $table->string('path');
            $table->unsignedBigInteger('id_status');
            $table->date('update_date');
            $table->timestamps();

            $table->foreign('id_status')->references('id_status')->on('aps_status')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('update_aps');
    }
}
