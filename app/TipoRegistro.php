<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoRegistro extends Model
{
    public $table = 'tipo_registro';  

	protected $fillable = [
        'idBacenWebService', 'descricao'     
    ]; 

    public $timestamps = false;  

}
