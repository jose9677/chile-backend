<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('id_document_type');
            $table->string('document_path');
            $table->string('document_ext');
            $table->timestamps();

            $table->foreign('id_request')->references('id')->on('solicitudes')->onUpdate('cascade');
            $table->foreign('id_document_type')->references('id')->on('documents_types')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
