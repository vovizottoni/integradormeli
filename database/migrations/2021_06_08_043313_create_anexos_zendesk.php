<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnexosZendesk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anexos_zendesk', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->bigInteger('encaminhamentos_id')->unsigned();  
            $table->foreign('encaminhamentos_id')->references('id')->on('encaminhamentos'); 

            $table->text('resposta')->nullable();                
            $table->binary('arquivo1')->nullable();
            $table->binary('arquivo2')->nullable();
            $table->binary('arquivo3')->nullable();         

        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anexos_zendesk');
    }
}
