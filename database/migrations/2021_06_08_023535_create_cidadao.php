<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCidadao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cidadao', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->bigInteger('demandas_id')->unsigned();  
            $table->foreign('demandas_id')->references('id')->on('demandas'); 

            $table->string('nome', 250)->nullable();
            $table->string('documento', 50)->nullable();
            $table->string('tipoDocumento', 50)->nullable();
            $table->string('emails', 250)->nullable();
            $table->string('telefones', 250)->nullable();
            $table->string('cep', 20)->nullable();
            $table->string('uf', 200)->nullable();
            $table->string('municipio_descricao', 250)->nullable();            
            $table->bigInteger('municipio_id')->nullable();  
            $table->string('endereco', 250)->nullable();            
            $table->string('bairro', 250)->nullable();                        
            $table->string('numero', 40)->nullable();            
            $table->string('complemento', 250)->nullable();            
            $table->string('cidadeUF', 250)->nullable();            
            
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cidadao');
    }
}
