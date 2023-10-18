<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCidadaoVarchar250 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('cidadao', function (Blueprint $table) {
         
            $table->string('documento', 250)->change();
            $table->string('tipoDocumento', 250)->change();
            $table->string('cep', 250)->change();
            $table->string('numero', 250)->change(); 

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
