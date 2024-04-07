<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modify_users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('rut');
            $table->string('names');
            $table->string('lastname');
            $table->date('f_nac');
            $table->string('email');
            $table->string('password');
            $table->integer('landline');
            $table->integer('mobile_phone');
            $table->boolean('employee');
            $table->string('supervisor_rut')->nullable();
            $table->string('otp')->nullable();
            $table->boolean('email_active');
            $table->string('profile_pic')->nullable();
            $table->unsignedBigInteger('id_rol');
            $table->string('api_token')->nullable();
            $table->boolean('active')->nullable();
            $table->boolean('aps');
            $table->unsignedBigInteger('id_sex');
            $table->integer('percent');
            $table->string('origin');
            $table->string('rut_employee');
            $table->boolean('approve');
            $table->unsignedBigInteger('id_mod_status')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('verificated_by')->nullable();
            $table->string('created_by')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('modify_users');
    }
}
