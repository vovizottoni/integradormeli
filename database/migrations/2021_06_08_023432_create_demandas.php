<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemandas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demandas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('instituicao_financeira_id')->unsigned();  
            $table->foreign('instituicao_financeira_id')->references('id')->on('instituicao_financeira'); 

            $table->bigInteger('situacao_bacen_id')->unsigned();  
            $table->foreign('situacao_bacen_id')->references('id')->on('situacao_bacen'); 

            $table->bigInteger('canal_atendimento_id')->unsigned()->nullable();  
            $table->foreign('canal_atendimento_id')->references('id')->on('canal_atendimento'); 

            $table->bigInteger('motivo_id')->unsigned()->nullable();  
            $table->foreign('motivo_id')->references('id')->on('motivo'); 

            $table->bigInteger('tipo_registro_id')->unsigned()->nullable();  
            $table->foreign('tipo_registro_id')->references('id')->on('tipo_registro'); 

            $table->string('numeroDemanda', 200)->nullable();
            $table->bigInteger('idInternoBacenWebService')->unsigned();  
            $table->boolean('indicadorLido')->default(0);            
            $table->dateTime('dataCadastro')->nullable();
            $table->dateTime('dataDisponibilizacao')->nullable();
            $table->dateTime('dataNotificacao')->nullable();
            $table->dateTime('dataProtocolo')->nullable();
            $table->boolean('protocoloIF')->nullable();            
            $table->text('descricao')->nullable();
            $table->dateTime('prazo')->nullable();
            $table->string('situacao_no_robo', 30)->nullable(); 

        });                   

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demandas');
    }
}
