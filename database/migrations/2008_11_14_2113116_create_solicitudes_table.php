<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function PHPUnit\Framework\once;

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

            $table->timestamps();

            $table->foreign('rut_user')->references('rut')->on('users')->onUpdate('cascade');
            $table->foreign('rut_employee')->references('rut')->on('users')->onUpdate('cascade');
            $table->foreign('id_property_type')->references('id')->on('property_types')->onUpdate('cascade');
            $table->foreign('id_location')->references('id')->on('locations')->onUpdate('cascade');
            $table->foreign('id_status')->references('id')->on('status')->onUpdate('cascade');
            $table->foreign('id_prevention_type_detail')->references('id')->on('preventions_details')->onUpdate('cascade');
            $table->foreign('id_motive')->references('id_motive')->on('motives')->onUpdate('cascade');
            $table->foreign('id_dedication')->references('id_dedication')->on('dedications')->onUpdate('cascade');
            $table->foreign('id_remuneration')->references('id_remuneration')->on('remunerations')->onUpdate('cascade');
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
