<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_actions', function (Blueprint $table) {
            $table->string('rut');
            $table->unsignedBigInteger('id_action');
            $table->timestamps();

            $table->foreign('rut')->references('rut')->on('users')->onUpdate('cascade');
            $table->foreign('id_action')->references('id')->on('actions')->onUpdate('cascade');

            $table->primary(['rut', 'id_action']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_actions');
    }
}
