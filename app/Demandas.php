<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Demandas extends Model
{
    public $table = 'demandas';  

	protected $fillable = [
        'instituicao_financeira_id', 'situacao_bacen_id', 'canal_atendimento_id', 'motivo_id', 'tipo_registro_id', 'numeroDemanda',
        'idInternoBacenWebService', 'indicadorLido', 'dataCadastro', 'dataDisponibilizacao', 'dataNotificacao', 'dataProtocolo', 'protocoloIF',
        'descricao', 'prazo', 'situacao_no_robo', 'zendesk_requester_id', 'zendesk_ticket_id'
    ];          
    
    public $timestamps = false;
    
}
