<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnexosBacen extends Model
{
    public $table = 'anexos_bacen';  

	protected $fillable = [
        'encaminhamentos_id', 'idBacenWebService', 'nomeBacenWebService', 'urlBacenWebService', 'arquivo', 'descricao'
    ];   

    public $timestamps = false;

}
