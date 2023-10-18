<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnexosZendesk extends Model
{
    public $table = 'anexos_zendesk';  

	protected $fillable = [
        'encaminhamentos_id', 'resposta', 'arquivo1', 'arquivo2', 'arquivo3'
    ]; 

    public $timestamps = false;

}
