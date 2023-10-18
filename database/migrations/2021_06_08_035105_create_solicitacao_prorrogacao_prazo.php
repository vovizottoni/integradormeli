<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitacaoProrrogacaoPrazo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitacao_prorrogacao_prazo', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->bigInteger('demandas_id')->unsigned();  
            $table->foreign('demandas_id')->references('id')->on('demandas'); 

            $table->text('justificativa')->nullable();
            $table->dateTime('prazoSugerido')->nullable();
            $table->set('utiliza_anexo', ['s', 'n'])->default('n');
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
        Schema::dropIfExists('solicitacao_prorrogacao_prazo');
    }
}
