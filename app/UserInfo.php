<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    public $table = 'users_info';
    protected $fillable = [
      'user_id', 'cpf', 'rg', 'data_nascimento', 'sexo' , 'telefoneCelular' , 'telefoneFixo' , 'cep' , 'estado' , 'cidade'  , 'bairro'   , 'logradouro'   , 'numero' ]; 

    //desabilita created_at e updated_at
    public $timestamps = false;  

}  
