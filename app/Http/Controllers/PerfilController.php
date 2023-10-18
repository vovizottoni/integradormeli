<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


//Recursos utilizados (Hash, Session) 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;        
use Session;  


//para deletar arquivos do storage
use Illuminate\Support\Facades\Storage;  

//para lançar erros manualmente no validate  (forçar o erro)   
use Illuminate\Validation\ValidationException;    



//Models utilizadas devem ser definidas aqui
use App\User; 
use App\UserInfo;

class PerfilController extends Controller
{
    public function editar(){

        
        //consulta informações adicionais do usuário (tabela users_info)
        $userInfo = UserInfo::where([['user_id', '=', Auth::user()->id]])->first(); 
        
        

        
        return view('perfil.editar', compact('userInfo')); 
    }  

    public function edita(Request $req){  
        
        

        
        //implementa o validate   
        $regras = [
            'nome' => 'required|max:150',
            'email' => 'required|email|unique:users,email,'.Auth::user()->id,   //unique: este campo deve ser único, considerando a tabela.campo: users.email e o id do registro na sessão Auth::user()->id
            'senha' => 'sometimes|nullable|min:7|max:9|alpha_num'       //senha só tem validate se estiver preenchida. Este critério acontece utilizando: sometimes|nullable no inicio da regra            
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
        
        
        //#### Processa arquivo imagem #### 
            //inicializa o $path_atual com o que está no BD
            $path_atual = User::where([['id', '=', Auth::user()->id]])->first()->imagem;              
            $path_atual = $path_atual?$path_atual:NULL;
             
            if($req->file("fotoPerfil") && $req->file("fotoPerfil")->isValid()){ //se existe arquivo e o arquivo não está corrompido
                
                //obtém extensão do arquivo
                $extension = $req->file('fotoPerfil')->extension();
                $extension = strtolower($extension);                
                //checka estensões permitidas
                $extensoesPermitidas = ['jpg', 'png', 'jpeg'];
                if(!in_array($extension, $extensoesPermitidas)){                    
                    
                    //força uma mensagem de invalidate no input file
                    //retorna para a view sem prosseguir

                    //lança o erro manualmente
                    throw ValidationException::withMessages(['fotoPerfil' => 'Tipo de arquivo não permitido']);
                                            
                }

                $imagemObjeto = $req->file("fotoPerfil"); //Extrai imagem na forma de Obj. Obj contem infos da img
                $path_atual = $imagemObjeto->store('perfil', 'public'); //salva o arquivo em storage/app/public/perfil/nameimg.extension    (o disco public do laravel é mais recomendado pois permite que a imagem seja requerida por clientes externos como navegador etc, usando metodo url() por exemplo )  (se o diretório 'perfil' não existisse, o laravel criaria ele)  (o retorno é $path = 'perfil/nameimg.extension' e deverá ser salvo no BD)  
               
            }else{

            }
        //#################################
        


        $dados = $req->all();  
        
        
        //consulta senha_atual no BD
        $senha_atual = User::where([['id', '=', Auth::user()->id]])->first()->password; 


        //Edita  
        User::where([['id', '=', Auth::user()->id]])->update(['name' => $dados['nome'], 'email' => $dados['email'], 'password' => $dados['senha']?Hash::make($dados['senha']):$senha_atual, 'imagem' => $path_atual ]);           
           

        //mensagem de sucesso                          
        Session::flash('message', 'Perfil editado com sucesso!'); 
        Session::flash('alert-class', 'alert-success'); 

        return redirect()->route('perfil.editar'); 
        

    }

    public function excluiImagem(){

        //exclui imagem do usuário logado (BD e localstorage)


        //se existe imagem salva no BD
        if(Auth::user()->imagem){            

            //apaga do storage/app/public
            Storage::disk('public')->delete(Auth::user()->imagem);   // perfil/nomeImg.ext     

            //apaga do BD
            User::where([['id', '=', Auth::user()->id]])->update(['imagem' => NULL]);

            //mensagem de sucesso                          
            Session::flash('message', 'Imagem excluída com sucesso!');  
            Session::flash('alert-class', 'alert-success'); 
        }

        return redirect()->route('perfil.editar');

    }

    public function editaInfo(Request $req){
        

        //implementa o validate   
        $regras = [
            'data_nascimento' => 'sometimes|nullable|date_format:"d/m/Y"'   //Só se tiver preenchido, que aplica-se o validate (sometimes|nullable). 
         
        ];
        $mensagens_erro = [ 
            'date_format' => 'Informe uma data válida'
            
        ];
        $req->validate($regras, $mensagens_erro); 

        

        $dados = $req->all();
        

        //preparação de dados
        //cpf
        $dados['cpf'] = str_replace('-', '', str_replace('.', '' , $dados['cpf']));        
        //data
        $dados['data_nascimento'] =  implode('-', array_reverse(explode('/', $dados['data_nascimento'])));        
        //remove o token   
        unset($dados['_token']);             

        //Edita
        UserInfo::where([['user_id', '=', Auth::user()->id]])->update($dados);           


        //mensagem de sucesso                          
        Session::flash('message', 'Informações adicionais editadas com sucesso!');   
        Session::flash('alert-class', 'alert-success'); 

        return redirect()->route('perfil.editar'); 

    }


}
