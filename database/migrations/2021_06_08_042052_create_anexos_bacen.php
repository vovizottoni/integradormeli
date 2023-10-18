<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnexosBacen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anexos_bacen', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->bigInteger('encaminhamentos_id')->unsigned();  
            $table->foreign('encaminhamentos_id')->references('id')->on('encaminhamentos'); 
            
            $table->bigInteger('idBacenWebService');                    
            $table->string('nomeBacenWebService', 250)->nullable();    
            $table->string('urlBacenWebService', 250)->nullable();                
            $table->binary('arquivo')->nullable();
            $table->text('descricao')->nullable();                

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anexos_bacen');
    }
}
