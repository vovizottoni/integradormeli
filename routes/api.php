<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
| 
*/
 


Route::middleware('auth:api')->group(function(){   //auth:api => passport do laravel   

   //RoboMeliController  - Define rotas que estarão disponiveis para o zendesk Acessar (c/ token do passport)
   
});

   
//ROTAS DESPROTEGIDAS de OAUTH -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
Route::post('/robomeli/enviaRespostaParaBacen', ['as' => 'robomeli.enviaRespostaParaBacen', 'uses' => 'RoboMeliController@enviaRespostaParaBacen']);
Route::post('/robomeli/enviaSolicitacaoPrazoParaBacen', ['as' => 'robomeli.enviaSolicitacaoPrazoParaBacen', 'uses' => 'RoboMeliController@enviaSolicitacaoPrazoParaBacen']);
   

// Rota de testes oauth-passport e para BALANCEADOR DE CARGA DE PRODUÇÃO 
Route::any('/robomeli/rotaComOAuth', ['as' => 'robomeli.rotaComOAuth', 'uses' => 'RoboMeliController@rotaComOAuth']);
