<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstituicaoFinanceira extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //cria tabela
        Schema::create('instituicao_financeira', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idBacenWebService')->unsigned();  
            $table->string('cnpj', 14)->nullable();
            $table->string('nome', 250)->nullable(); 
            
        });   

        //insere registro padrão
        DB::table('instituicao_financeira')->insert(
            [
                ['idBacenWebService' => 9999382771, 'cnpj' => '10573521', 'nome' => 'MERCADOPAGO.COM REPRESENTACOES LTDA.']
            ]
        ); 


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instituicao_financeira');
    }
}
