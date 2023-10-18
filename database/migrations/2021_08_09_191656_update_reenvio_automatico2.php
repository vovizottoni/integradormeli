<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReenvioAutomatico2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE  reenvio_automatico  MODIFY COLUMN arquivo1 LONGBLOB');
        DB::statement('ALTER TABLE  reenvio_automatico  MODIFY COLUMN arquivo2 LONGBLOB');
        DB::statement('ALTER TABLE  reenvio_automatico  MODIFY COLUMN arquivo3 LONGBLOB');

        Schema::table('reenvio_automatico', function (Blueprint $table) {
                     
            $table->text('msg')->change();                  

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
