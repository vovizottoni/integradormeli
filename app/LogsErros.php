<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogsErros extends Model
{
    public $table = 'logs_erros';  

	protected $fillable = [
        'comando', 'data', 'descricao', 'codigo', 'demandas_id'   
    ]; 

    public $timestamps = false;
}
