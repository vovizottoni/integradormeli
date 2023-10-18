<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CanalAtendimento extends Model
{
    public $table = 'canal_atendimento';  

	protected $fillable = [
        'idBacenWebService', 'descricao'  
    ]; 

    public $timestamps = false; 

}
