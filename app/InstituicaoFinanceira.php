<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InstituicaoFinanceira extends Model
{
    public $table = 'instituicao_financeira';  

	protected $fillable = [
        'idBacenWebService', 'cnpj', 'nome'
    ]; 

    public $timestamps = false;
    
}
