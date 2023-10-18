<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogsComandos extends Model
{
    
    public $table = 'logs_comandos';  

	protected $fillable = [
        'comando', 'data', 'duracao', 'totalDemandasRetornadasRDRWebservice'
    ]; 

    public $timestamps = false;     
    
}
