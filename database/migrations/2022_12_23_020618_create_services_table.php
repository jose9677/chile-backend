<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->integer('cod');
            $table->text('description');
            $table->unsignedBigInteger('id_category');
            $table->unsignedBigInteger('id_provider');
            $table->timestamps();

            $table->foreign('id_category')->references('id')->on('categories')->onUpdate('cascade');
            $table->foreign('id_provider')->references('id_provider')->on('providers')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
