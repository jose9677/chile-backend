<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles_actions', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_action');
            $table->timestamps();

            $table->foreign('id_rol')->references('id')->on('roles')->onUpdate('cascade');
            $table->foreign('id_action')->references('id')->on('actions')->onUpdate('cascade');

            $table->primary(['id_rol', 'id_action']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_actions');
    }
}
