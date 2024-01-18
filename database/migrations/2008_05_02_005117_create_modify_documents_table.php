<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModifyDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modify_documents', function (Blueprint $table) {
            $table->id('id_document');
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('id_document_type');
            $table->string('document_path');
            $table->string('document_ext');
            $table->boolean('active');
            $table->string('rut_employee');
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
        Schema::dropIfExists('modify_documents');
    }
}
