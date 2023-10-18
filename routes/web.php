<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);   

Route::get('/home', 'HomeController@index')->name('home');


//Logout via get
Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');




//gestão de usuários (admins)

Route::get('/usuarios', ['as' => 'usuarios', 'uses' => 'UsuarioController@exibir']);
Route::any('/usuarios/filtrar',  ['as' => 'usuarios.filtrar' , 'uses' => 'UsuarioController@filtrar']);        
Route::get('/usuarios/cadastrar', ['as' => 'usuarios.cadastrar', 'uses' => 'UsuarioController@cadastrar']);
Route::post('/usuarios/cadastra', ['as' => 'usuarios.cadastra', 'uses' => 'UsuarioController@cadastra']);
Route::get('/usuarios/editar/{id}', ['as' => 'usuarios.editar', 'uses' => 'UsuarioController@editar']);
Route::post('/usuarios/edita', ['as' => 'usuarios.edita', 'uses' => 'UsuarioController@edita']);
Route::get('/usuarios/excluir/{id}', ['as' => 'usuarios.excluir', 'uses' => 'UsuarioController@exclui']);
Route::get('/usuarios/limparfiltro', ['as' => 'usuarios.limparfiltro', 'uses' => 'UsuarioController@limparfiltro']);


//Meu Perfil

Route::get('/perfil/editar', ['as' => 'perfil.editar', 'uses' => 'PerfilController@editar' ]);
Route::post('/perfil/edita', ['as' => 'perfil.edita', 'uses' => 'PerfilController@edita' ]);
Route::get('/perfil/exclui/imagem', ['as' => 'perfil.exclui.imagem', 'uses' => 'PerfilController@excluiImagem']);
Route::post('/perfil/editaInfo', ['as' => 'perfil.editaInfo', 'uses' => 'PerfilController@editaInfo']); 
