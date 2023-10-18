<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsComandos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs_comandos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('comando', 250)->nullable();
            $table->dateTime('data')->nullable();
            $table->bigInteger('duracao')->nullable();    
            $table->bigInteger('totalDemandasRetornadasRDRWebservice')->nullable();  

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs_comandos');
    }
}
