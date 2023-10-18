<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsErros extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs_erros', function (Blueprint $table) {
            
            $table->bigIncrements('id');
            $table->string('comando', 250)->nullable();
            $table->dateTime('data')->nullable();
            $table->string('descricao', 250)->nullable();
            $table->bigInteger('codigo')->nullable();
            $table->bigInteger('demandas_id')->nullable();   

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs_erros');
    }
}
