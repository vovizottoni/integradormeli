<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReenvioAutomatico extends Model
{
    public $table = 'reenvio_automatico';  

	protected $fillable = [

        'encaminhamentos_id', 'resposta', 'arquivo1', 'arquivo2', 'arquivo3', 'tentativas', 'sucesso', 'msg',
        'instituicaofinanceira_idbacenwebservice', 'zendesk_ticket_id', 'fileName1', 'fileName2', 'fileName3', 'app_agente_id' 

    ];                         
    
    public $timestamps = false; 
}
