<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCanalAtendimento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('canal_atendimento', function (Blueprint $table) {
            $table->bigIncrements('id');       
            $table->bigInteger('idBacenWebService')->unsigned();  
            $table->string('descricao', 250)->nullable();    
        }); 

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('canal_atendimento');
    }
}
