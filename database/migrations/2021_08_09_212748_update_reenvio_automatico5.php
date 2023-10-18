<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReenvioAutomatico5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reenvio_automatico', function (Blueprint $table) {
                        
            $table->string('fileName1', 250)->nullable();
            $table->string('fileName2', 250)->nullable();
            $table->string('fileName3', 250)->nullable();                   

            $table->bigInteger('app_agente_id')->nullable();   
            
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
