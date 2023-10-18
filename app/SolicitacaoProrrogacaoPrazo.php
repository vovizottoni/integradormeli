<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolicitacaoProrrogacaoPrazo extends Model
{
    public $table = 'solicitacao_prorrogacao_prazo';  

	protected $fillable = [
        'demandas_id', 'justificativa', 'prazoSugerido', 'utiliza_anexo', 'arquivo1', 'arquivo2', 'arquivo3'      
    ]; 


    public $timestamps = false;

}
