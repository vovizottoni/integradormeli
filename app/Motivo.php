<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Motivo extends Model
{
    public $table = 'motivo';  

	protected $fillable = [
        'idBacenWebService', 'descricao' 
    ]; 

    public $timestamps = false;

}
