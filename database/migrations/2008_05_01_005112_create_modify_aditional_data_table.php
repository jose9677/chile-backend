<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifyAditionalDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modify_aditional_data', function (Blueprint $table) {
            $table->id('id_mod');
            $table->unsignedBigInteger('id_request');
            $table->boolean('carer');
            $table->string('comments');
            $table->integer('percent');
            $table->boolean('aps');
            $table->unsignedBigInteger('id_motive');
            $table->unsignedInteger('id_dedication');
            $table->unsignedInteger('id_remuneration');
            $table->string('origin');
            $table->boolean('approve')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->string('verificated_by')->nullable();
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
        Schema::dropIfExists('modify_aditional_data');
    }
}
