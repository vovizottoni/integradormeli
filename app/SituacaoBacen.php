<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SituacaoBacen extends Model
{
    public $table = 'situacao_bacen';  

	protected $fillable = [
         'idBacenWebService', 'descricao', 'alteravelIF' 
    ]; 

    public $timestamps = false;

}
