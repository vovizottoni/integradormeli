<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReenvioAutomatico extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reenvio_automatico', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->bigInteger('encaminhamentos_id')->unsigned();  
            
            $table->text('resposta')->nullable();                
            $table->binary('arquivo1')->nullable();
            $table->binary('arquivo2')->nullable();
            $table->binary('arquivo3')->nullable();         

            $table->bigInteger('tentativas')->unsigned();  

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
