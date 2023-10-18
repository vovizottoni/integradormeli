<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAnexosZendeskLongBlob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Modifica Colunas BLOB para MEDIUMBLOB  
                        
        DB::statement('ALTER TABLE anexos_zendesk MODIFY COLUMN arquivo1 MEDIUMBLOB');
        DB::statement('ALTER TABLE anexos_zendesk MODIFY COLUMN arquivo2 MEDIUMBLOB');
        DB::statement('ALTER TABLE anexos_zendesk MODIFY COLUMN arquivo3 MEDIUMBLOB');

        DB::statement('ALTER TABLE anexos_bacen MODIFY COLUMN arquivo MEDIUMBLOB');             
        
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
