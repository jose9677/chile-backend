<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('apigee_refresh_token');
            $table->string('apigee_token');
            $table->integer('apigee_token_expires');
            $table->integer('apigee_refresh_token_expires');
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
        Schema::dropIfExists('google_tokens');
    }
}
