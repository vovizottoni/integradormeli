<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldsToLongBlob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        //Modifica Colunas MEDIUMBLOB para   LONGBLOB
                        
        DB::statement('ALTER TABLE anexos_zendesk MODIFY COLUMN arquivo1 LONGBLOB');
        DB::statement('ALTER TABLE anexos_zendesk MODIFY COLUMN arquivo2 LONGBLOB');
        DB::statement('ALTER TABLE anexos_zendesk MODIFY COLUMN arquivo3 LONGBLOB');

        DB::statement('ALTER TABLE anexos_bacen MODIFY COLUMN arquivo LONGBLOB');             
                        

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('long_blob', function (Blueprint $table) {
            //
        });
    }
}
