<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAnexosZendeskMimeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('anexos_zendesk', function (Blueprint $table) {
            
            $table->string('arquivo1_mimetype', 250)->nullable();
            $table->string('arquivo2_mimetype', 250)->nullable();
            $table->string('arquivo3_mimetype', 250)->nullable();                   

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
