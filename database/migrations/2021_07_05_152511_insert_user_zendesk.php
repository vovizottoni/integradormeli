<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertUserZendesk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::table('users')->insert(
            array(
                'name' => 'Zendesk Client',    
                'email' => 'zendesk_clientt@zendesk.com',
                'password' => Hash::make('zendesk_zendesk226'),  
                
            )
        );

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
