<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Encaminhamentos extends Model
{
    public $table = 'encaminhamentos';  

	protected $fillable = [
        'demandas_id', 'idEncaminhamentoBacenWebService'      
    ]; 

    public $timestamps = false;

}
