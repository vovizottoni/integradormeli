<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsersInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_info', function (Blueprint $table) {
            $table->bigIncrements('id');            

            $table->bigInteger('user_id')->unsigned();  
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->string('cpf', 50)->nullable();
            $table->string('rg', 50)->nullable();            
            $table->date('data_nascimento')->nullable();
            $table->set('sexo', ['m', 'f'])->nullable();

            $table->string('telefoneCelular', 50)->nullable();
            $table->string('telefoneFixo', 50)->nullable();

            $table->string('cep', 50)->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cidade')->nullable();
            $table->string('bairro')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();            
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_info');
    }
}
