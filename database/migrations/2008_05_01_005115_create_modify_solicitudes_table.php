<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifySolicitudesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modify_solicitudes', function (Blueprint $table) {
            $table->id('id_solicitude');
            $table->string('rut_user');
            $table->unsignedBigInteger('id_prevention_type_detail');
            $table->boolean('assurance');
            $table->unsignedBigInteger('id_location');
            $table->integer('way_number');
            $table->unsignedBigInteger('id_property_type');
            $table->string('property_number');
            $table->boolean('terms_condition')->nullable();
            $table->timestamp('request_date')->nullable();
            $table->unsignedBigInteger('id_status');
            $table->string('rut_employee')->nullable();
            $table->timestamp('visit_date')->nullable();
            $table->boolean('applicant_at_home')->nullable();
            $table->boolean('prevention_is_valid')->nullable();
            $table->boolean('location_is_valid')->nullable();
            $table->string('comments')->nullable();
            $table->boolean('is_active');
            $table->boolean('rut_match');
            $table->boolean('carer')->nullable();
            $table->unsignedBigInteger('id_motive')->nullable();
            $table->unsignedBigInteger('id_dedication')->nullable();
            $table->unsignedBigInteger('id_remuneration')->nullable();
            $table->date('admission_date');
            $table->string('dir_wots')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->boolean('approve');
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('id_mod_status')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('verificated_by')->nullable();
            $table->unsignedBigInteger('prevention_type');
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modify_solicitudes');
    }
}
