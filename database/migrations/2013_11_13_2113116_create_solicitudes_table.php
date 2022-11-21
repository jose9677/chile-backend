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
            $table->string('assurance');
            $table->unsignedBigInteger('id_location');
            $table->integer('way_number');
            $table->unsignedBigInteger('id_property_type');
            $table->integer('property_number');
            $table->boolean('terms_condition');
            $table->date('request_date');
            $table->unsignedBigInteger('id_status');
            $table->boolean('applicant_at_home');
            $table->boolean('prevention_is_valid');
            $table->boolean('location_is_valid');
            $table->string('comments')->nullable();
            $table->boolean('is_active');

            $table->timestamps();

            $table->foreign('id_property_type')->references('id')->on('property_types')->onUpdate('cascade');
            $table->foreign('id_location')->references('id')->on('locations')->onUpdate('cascade');
            $table->foreign('id_status')->references('id')->on('status')->onUpdate('cascade');
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
        Schema::dropIfExists('solicitudes');
    }
}
