<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


   
//Recursos utilizados (Hash, Session)      
use Illuminate\Support\Facades\Hash;   
use Session;


//Models utilizadas devem ser definidas aqui
use App\User; 
use App\UserInfo;

class UsuarioController extends Controller
{
    
    public function exibir(){ 

        
        //checka se tem filtro na sessão
        $filtroSessao = session()->get('filtroPesquisaPaginate_usuarios');
        if($filtroSessao){
            $filtros = $filtroSessao;    

             //Inicializa o queryBuilder (para construir a consulta)
             $queryBuilder = User::query();        

            //se tem filtro x, adiciona filtro x na consulta        
            if(isset($filtros['nome']) && $filtros['nome']){

                $queryBuilder = $queryBuilder->where('name', 'like', '%'.$filtros['nome'].'%');    

            }
            //se tem filtro y, adiciona filtro y na consulta        
            if(isset($filtros['email']) && $filtros['email']){ 

                $queryBuilder = $queryBuilder->where('email', 'like', '%'.$filtros['email'].'%');    

            } 
            //se tem filtro z, adiciona filtro z na consulta        
            if(isset($filtros['id']) && $filtros['id']){     

                $queryBuilder = $queryBuilder->where('id', '=', $filtros['id']);     

            }            
            //se tivesse mais filtros, era só seguir o padrão da condição anterior   


            //Executa a consulta
            $usuarios = $queryBuilder->paginate(20);                  

        }else{
            $usuarios = User::paginate(20);                    
            $filtros = [];
        }                               

             
         

        return view('usuarios.exibir' , compact('usuarios', 'filtros'));
        
    }

    public function filtrar(Request $req){
        

        //obtém campos e valores 
        $filtros = $req->all();

        
        //escreve filtros na Sessão
        if($filtros){
            session()->put('filtroPesquisaPaginate_usuarios', $filtros);
        }


        //Inicializa o queryBuilder (para construir a consulta)
        $queryBuilder = User::query();

        

        //se tem filtro x, adiciona filtro x na consulta        
        if(isset($filtros['nome']) &&  $filtros['nome']){

            $queryBuilder = $queryBuilder->where('name', 'like', '%'.$filtros['nome'].'%');    

        }
        //se tem filtro y, adiciona filtro y na consulta        
        if(isset($filtros['email']) && $filtros['email']){

            $queryBuilder = $queryBuilder->where('email', 'like', '%'.$filtros['email'].'%');    

        }
        //se tem filtro z, adiciona filtro z na consulta        
        if(isset($filtros['id']) && $filtros['id']){     

            $queryBuilder = $queryBuilder->where('id', '=', $filtros['id']);    

        }


        //se tivesse mais filtros, era só seguir o padrão da condição anterior


        //Executa a consulta
        $usuarios = $queryBuilder->paginate(20);              

        //renderiza view 
        return view('usuarios.exibir', compact('usuarios', 'filtros'));
        
    }  

    public function cadastrar(){
        
        return view('usuarios.cadastrar');    
        
    }

    public function cadastra(Request $req){


        //implementa o validate
        $regras = [
            'nome' => 'required|max:150',
            'email' => 'required|email|unique:users,email',   //unique: este campo deve ser único, considerando a tabela.campo: users.email
            'senha' => 'required|min:7|max:9|alpha_num',       
            'tipo' => 'required'            
        ];
        $mensagens_erro = [ 
            'required' => 'Campo de preenchimento obrigatório',
            'nome.max' => 'Este campo deve ter no máximo 150 caracteres',
            'email'  => 'E-mail inválido',
            'senha.min' => 'Este campo deve ter no mínimo 7 caracteres',
            'senha.max' => 'Este campo deve ter no máximo 9 caracteres',  
            'alpha_num' => 'Este campo deve conter apenas letras e números',
            'unique' => 'Este email já está em uso'                              
        ];
        $req->validate($regras, $mensagens_erro);    

        

        //captura os dados
        //obtém campos e valores
        $dados = $req->all(); 

        //tenta Inserir
        $linhaInserida = User::create(['name' => $dados['nome'], 'email' => $dados['email'], 'password' => Hash::make($dados['senha']), 'tipo' => $dados['tipo']  ]); 
        //se precisar do id inseriro: $linhaInserida->id; 

        //cadastra users_info desse usuário
        UserInfo::create(['user_id' => $linhaInserida->id]);
                
        
        //mensagem de sucesso        
        Session::flash('message', 'Item cadastrado com sucesso!'); 
        Session::flash('alert-class', 'alert-success'); 
        

        return redirect()->route('usuarios');  

    }

    public function editar($id){
        
        //consulta usuario $id
        $usuario = User::where([['id', '=', $id]])->first();
        
        
        return view('usuarios.editar', compact('usuario'));  

    }

    public function edita(Request $req){

        
        //implementa o validate   
        $regras = [
            'nome' => 'required|max:150',
            'email' => 'required|email|unique:users,email,'.$req->all()['id'],   //unique: este campo deve ser único, considerando a tabela.campo: users.email e o id do registro $req->all()['id']
            'senha' => 'sometimes|nullable|min:7|max:9|alpha_num',       //senha só tem validate se estiver preenchida. Este critério acontece utilizando: sometimes|nullable no inicio da regra
            'tipo' => 'required'               
        ];
        $mensagens_erro = [ 
            'required' => 'Campo de preenchimento obrigatório',
            'nome.max' => 'Este campo deve ter no máximo 150 caracteres',
            'email'  => 'E-mail inválido',
            'senha.min' => 'Este campo deve ter no mínimo 7 caracteres',
            'senha.max' => 'Este campo deve ter no máximo 9 caracteres',  
            'alpha_num' => 'Este campo deve conter apenas letras e números',
            'unique' => 'Este email já está em uso'                               
        ];
        $req->validate($regras, $mensagens_erro);
        
        

        $dados = $req->all();
        //obtém id
        $id = $dados['id'];    
        unset($dados['id']);

        //consulta senha_atual no BD
        $senha_atual = User::where([['id', '=', $id]])->first()->password; 


        //Edita
        User::where([['id', '=', $id]])->update(['name' => $dados['nome'], 'email' => $dados['email'], 'password' => $dados['senha']?Hash::make($dados['senha']):$senha_atual ]);      
           

        //mensagem de sucesso                          
        Session::flash('message', 'Item editado com sucesso!'); 
        Session::flash('alert-class', 'alert-success'); 

        return redirect()->route('usuarios'); 
         
    }

    public function exclui($id){
        
        //Exclui         
        User::where([['id', '=', $id]])->delete();                       

        //mensagem de sucesso        
        Session::flash('message', 'Item excluído com sucesso!'); 
        Session::flash('alert-class', 'alert-success'); 

        return redirect()->route('usuarios'); 

    }

    public function limparfiltro(){

        //limpa sessão 
        session()->forget('filtroPesquisaPaginate_usuarios');

        return redirect()->route('usuarios');   

    }
    

}
