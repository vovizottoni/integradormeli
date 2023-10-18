<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cidadao extends Model
{
    public $table = 'cidadao';  

	protected $fillable = [
        'demandas_id', 'nome', 'documento', 'tipoDocumento', 'emails', 'telefones', 'cep', 'uf', 'municipio_descricao', 'municipio_id', 'endereco', 'bairro', 'numero', 'complemento', 'cidadeUF'
    ]; 
    
    public $timestamps = false;
    
}
