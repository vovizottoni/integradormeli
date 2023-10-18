<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoRegistro extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_registro', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idBacenWebService')->unsigned();  
            $table->string('descricao', 250)->nullable();
        });

        //insere registro(s) padrão
        DB::table('tipo_registro')->insert(
            [
                ['idBacenWebService' => 2, 'descricao' => 'Reclamação regulada'] 
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
        Schema::dropIfExists('tipo_registro');
    }
}
