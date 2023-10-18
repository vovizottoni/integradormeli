<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;  

//models Utilizadas  
use App\Demandas;
use App\Encaminhamentos;
use App\AnexosZendesk;
use App\LogsErros;
use App\LogsComandos;  
use App\SolicitacaoProrrogacaoPrazo; 
use App\InstituicaoFinanceira;           
use App\ReenvioAutomatico;

use Mail;

class RoboMeliController extends Controller
{


    public function rotaComOAuth(){      

        //sucesso
        return response()->json([
            'error' => false,  
            'message' => 'Requisicao bem sucedida a URL rotaComOAuth'    
        ]); 
    }
    
    public function enviaRespostaParaBacen(Request $req){


        set_time_limit(240); //240 seg
        ini_set("memory_limit", "128M"); //128M          

         
        

        //captura requisição POST  vinda do Zendesk APP     
        

        //EMAIL TO **************************
        $to_email = 'joao.peixoto.cd@gmail.com'; 
        //EMAIL TO **************************

        
        //instancia um httpCliente guzzle
        $parametro_SSL = '';
        if(env('AMBIENTE_LOCAL') == 's'){
            $parametro_SSL = ['verify' => false];
        }else{
            $parametro_SSL = []; 
        }
        $HttpClient = new \GuzzleHttp\Client($parametro_SSL);         



        
        //Processa arquivos 1, 2 (opcional) e 3 (opcional)
        try {
            
            //arquivo 1 (obrigatório)
            $fileName = $_FILES['file']['name'];  
            $fileType = $_FILES['file']['type'];
            $fileError = $_FILES['file']['error'];
            $fileContent = file_get_contents($_FILES['file']['tmp_name']);

            //arquivo 2 (opcional)
            $fileName2 = NULL;
            $fileType2 = NULL;
            $fileError2 = NULL;
            $fileContent2 = NULL;
            if(array_key_exists('file2', $_FILES) && $_FILES['file2']['name'] && $_FILES['file2']['type']){

                $fileName2 = $_FILES['file2']['name'];
                $fileType2 = $_FILES['file2']['type'];
                $fileError2 = $_FILES['file2']['error'];
                $fileContent2 = file_get_contents($_FILES['file2']['tmp_name']);                
            }

            //arquivo 3 (opcional)
            $fileName3 = NULL;
            $fileType3 = NULL;
            $fileError3 = NULL;
            $fileContent3 = NULL;
            if(array_key_exists('file3', $_FILES) && $_FILES['file3']['name'] && $_FILES['file3']['type']){

                $fileName3 = $_FILES['file3']['name'];
                $fileType3 = $_FILES['file3']['type'];
                $fileError3 = $_FILES['file3']['error'];
                $fileContent3 = file_get_contents($_FILES['file3']['tmp_name']);                
            }

        } catch (RuntimeException $e) {


            //LOG DE ERRO: Erro durante o processamento do arquivo. Verifique o tamanho e extensão dos arquivos a serem enviados.
            LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Erro durante o processamento do arquivo. Verifique o tamanho e extensão dos arquivos a serem enviados.'.$e->getMessage() , 'codigo' => 329  ]);


            return response()->json([ 
                'error' => $e->getMessage(),
                'message' => $e->getMessage()
            ]);            
            
        }   
        


        // // // // // // // // // // // // //
        if($_FILES['file']['size'] > 1000000){

            return response()->json([
                'error' => 'O anexo 1 deve ser menor que 1MB',
                'message' => 'O anexo 1 deve ser menor que 1MB'
            ]); 
        }
        

        if(array_key_exists('file2', $_FILES) && $_FILES['file2']['name'] && $_FILES['file2']['type']){
            if($_FILES['file2']['size'] > 1000000){

                return response()->json([
                    'error' => 'O anexo 2 deve ser menor que 1MB',
                    'message' => 'O anexo 2 deve ser menor que 1MB'
                ]); 
            }
        }     


        if(array_key_exists('file3', $_FILES) && $_FILES['file3']['name'] && $_FILES['file3']['type']){
            if($_FILES['file3']['size'] > 1000000){                  

                return response()->json([
                    'error' => 'O anexo 3 deve ser menor que 1MB',
                    'message' => 'O anexo 3 deve ser menor que 1MB'
                ]); 
            }
        }
        // // // // // // // // // // // // //                




        if($fileError == UPLOAD_ERR_OK  && ($fileError2 == NULL || $fileError2 == UPLOAD_ERR_OK) && ($fileError3 == NULL || $fileError3 == UPLOAD_ERR_OK) ){ //UPLOAD BEM SUCEDIDO

             

            //descobre o encaminhamentos_id a partir de app_ticket_id 
                //consulta demanda associada a esse ticket
                $demanda = Demandas::where([['zendesk_ticket_id', '=',  $_POST['app_ticket_id']]])->first();    
                
                if($demanda){ 

                    //descobre Instituicao dessa demanda
                    //descobre Instituicao dessa demanda
                    $InstituicaoFinanceira_DEMANDA = InstituicaoFinanceira::where([['id', '=', $demanda->instituicao_financeira_id]])->first();
                                        

                    //consulta Ultimo encaminhamento e verifica se ele ainda não foi respondido                    
                    $ultimoEncaminhamento = Encaminhamentos::where([['demandas_id', '=', $demanda->id]])->orderBy('id', 'DESC')->first();                    
                    $ultimoEncaminhamentoTemAnexo = AnexosZendesk::where([['encaminhamentos_id', '=', $ultimoEncaminhamento->id]])->first();
                    
                    if(empty($ultimoEncaminhamentoTemAnexo)){
                        
                                                                               

                        //Envia arquivo(s) para o RDRBACEN    
                        //************************** */    
                        //************************** */    
                        //************************** */        
                        
                            
                               
                        //Obtém informações de login do WEBSERVICE BACEN
                        $URL_WEBSERVICE_RDR_BACEN = env('URL_WEBSERVICE_RDR_BACEN_ATUALIZAR_DEMANDA');
                        

                        $USER_WEBSERVICE_RDR_BACEN = '';
                        $PASSWORD_WEBSERVICE_RDR_BACEN = ''; 
                        //se for MERCADOPAGO.COM REPRESENTACOES LTDA (CNPJ 10573521) , utiliza-se credenciais dela
                        if($InstituicaoFinanceira_DEMANDA->idBacenWebService == 9999382771){ 

                            $USER_WEBSERVICE_RDR_BACEN = env('USER_WEBSERVICE_RDR_BACEN');
                            $PASSWORD_WEBSERVICE_RDR_BACEN = env('PASSWORD_WEBSERVICE_RDR_BACEN');

                        //se for MERCADO CRÉDITO SOCIEDADE DE CRÉDITO, FINANCIAMENTO E INVESTIMENTO S.A. (CNPJ 37679449) , utiliza-se credenciais dela    
                        }else if($InstituicaoFinanceira_DEMANDA->idBacenWebService == 9999227337){

                            $USER_WEBSERVICE_RDR_BACEN = env('USER_WEBSERVICE_RDR_BACEN2');
                            $PASSWORD_WEBSERVICE_RDR_BACEN = env('PASSWORD_WEBSERVICE_RDR_BACEN2');

                        }else{

                            //LOG DE ERRO: Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes
                            LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes' , 'codigo' => 129 , 'demandas_id' => $demanda->id ]);
                            
                            //envia email
                            Mail::send(array(), array(), function ($message) use ($to_email){
                                $message->to($to_email)
                                ->subject('Log - integrador')
                                ->from('joao.peixoto.cd@gmail.com')  
                                ->setBody('<b>comando:</b> responderDemanda<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN indisponível ou credenciais desativadas/alteradas'.'<br><br><b>Código de erro:</b> 129', 'text/html');
                            });     
                                
                            return response()->json([
                                'error' => 'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes',
                                'message' => 'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes'
                            ]); 
                            

                        }   


                        //Instancia um SoapClient do PHP
                        $optionsSoap = [
                            'soap_version' => SOAP_1_1, 

                            'stream_context' => stream_context_create(array(
                                'ssl' => array(
                                    'crypto_method' =>  STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                                )
                            )),       

                            'exceptions' => true,
                            'trace' => 1,  //LIGA DEBUG do SOAP             
                            'login' => $USER_WEBSERVICE_RDR_BACEN,
                            'password' => $PASSWORD_WEBSERVICE_RDR_BACEN,              
                            'connection_timeout' => 60 //60 segundos aguardando o webservice RDR responder                       
                        ];

                        try{
                            
                            $soapClient = new \SoapClient($URL_WEBSERVICE_RDR_BACEN, $optionsSoap);              
                            
                        } catch(\Exception $e){
                                                                
                                //LOG DE ERRO: REGISTRAR INDISPONIBILIDADE DO WEBSERVICE NO NOSSO BD, ou credenciais desativadas/alteradas
                                LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas: '.$e->getMessage() , 'codigo' => 22 , 'demandas_id' => $demanda->id  ]);

                            //envia email
                            Mail::send(array(), array(), function ($message) use ($to_email){
                                $message->to($to_email)
                                ->subject('Log - integrador')
                                ->from('joao.peixoto.cd@gmail.com')  
                                ->setBody('<b>comando:</b> responderDemanda<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN indisponível ou credenciais desativadas/alteradas'.'<br><br><b>Código de erro:</b> 22', 'text/html');
                            });  


                            //Registra reenvio automatico
                            $verificacao_Reenvio = ReenvioAutomatico::where([['encaminhamentos_id', '=', $ultimoEncaminhamento->idEncaminhamentoBacenWebService]])->first();    
                            if(empty($verificacao_Reenvio)){  

                                

                                 ReenvioAutomatico::create([ 'encaminhamentos_id' => $ultimoEncaminhamento->idEncaminhamentoBacenWebService, 'resposta' => $_POST['respostaAPP'],  'arquivo1' => $fileContent, 'arquivo2' => $fileContent2, 'arquivo3' => $fileContent3 ,   'tentativas' => 0 , 'sucesso' => 0, 'msg' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas: '  .$e->getMessage()  , 'instituicaofinanceira_idbacenwebservice' => $InstituicaoFinanceira_DEMANDA->idBacenWebService ,  'zendesk_ticket_id' =>  $_POST['app_ticket_id'] , 'fileName1' => $fileName, 'fileName2' => $fileName2 , 'fileName3' => $fileName3 , 'app_agente_id' => $_POST['app_agente_id']   ]);
                                                          
                                //Cria comentario - reenvio
                                 
                                try{ 
                                    
                                    $body = new \StdClass();
                                    $body->ticket = new \StdClass();                                                                                                
                                    $body->ticket->comment = new \StdClass();
                                    $body->ticket->comment->body = "O envio ao Bacen falhou, uma nova tentativa automática será executada em 15 minutos. \n Nenhuma ação é necessária"; 
                                    $body->ticket->comment->public = false;
                                    $body->ticket->comment->author_id = $_POST['app_agente_id'];
                                    

                                    $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'.$_POST['app_ticket_id']   , [
                                        'headers' => [
                                            'Accept' => 'application/json',
                                            'Content-Type' => 'application/json',
                                        ],
                                        'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                        'body' => json_encode($body) 
                                    ]);

                                    //Obtem id desse usuario recem criado
                                    $createdComentZendesk = $res4->getBody(); 
                                    $createdComentZendeskTratado = json_decode((string) $createdComentZendesk);    
                                
                                }catch(\Exception $e){
                                                                
                                                                    
                                }  
                                

                            }
                                                                                 

                            
                            return response()->json([
                                'error' => 'WebService RDR BACEN indisponivel ou credenciais desativadas/alteradas.',
                                'message' => 'WebService RDR BACEN indisponivel ou credenciais desativadas/alteradas'
                            ]); 
                               

                        }


                        //^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        //ENvia resposta ao BACEN
                        //^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        $anexos_BACENN = [];
                        if(array_key_exists('file', $_FILES) && $_FILES['file']['name'] && $_FILES['file']['type']){

                            $objt1 = new \StdClass();
                            $objt1->nomeArquivo = $fileName;
                            $objt1->conteudoBase64 = base64_encode($fileContent);
                            $anexos_BACENN[] = $objt1;
                        }
                        if(array_key_exists('file2', $_FILES) && $_FILES['file2']['name'] && $_FILES['file2']['type']){

                            $objt2 = new \StdClass();
                            $objt2->nomeArquivo = $fileName2;
                            $objt2->conteudoBase64 = base64_encode($fileContent2);
                            $anexos_BACENN[] = $objt2;
                        }
                        if(array_key_exists('file3', $_FILES) && $_FILES['file3']['name'] && $_FILES['file3']['type']){     

                            $objt3 = new \StdClass();
                            $objt3->nomeArquivo = $fileName3;
                            $objt3->conteudoBase64 = base64_encode($fileContent3);
                            $anexos_BACENN[] = $objt3;
                        }
                        $parametros = [  
                            'resposta' => $_POST['respostaAPP'],                              
                            'idEncaminhamento' => $ultimoEncaminhamento->idEncaminhamentoBacenWebService, 
                            'anexos' => $anexos_BACENN 
                        ];


                        try{
                            $resposta_responderBase64Anexos = $soapClient->responderBase64Anexos($parametros);    
                        } catch(\Exception $e){    
                            
                            
                           //LOG DE ERRO: Erro ao tentar responder Demanda
                           LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Erro ao tentar enviar resposta ao WebService RDR BACEN: '.$e->getMessage() , 'codigo' => 23  , 'demandas_id' => $demanda->id  ]);
                           
                           //envia email
                           $e_current_message = $e->getMessage();
                           Mail::send(array(), array(), function ($message) use ($to_email, $e_current_message){
                            $message->to($to_email)
                            ->subject('Log - integrador')
                            ->from('joao.peixoto.cd@gmail.com')  
                            ->setBody('<b>comando:</b> responderDemanda<br><br>'.'<b>Mensagem:</b> Erro ao tentar enviar resposta ao WebService RDR BACEN: '.$e_current_message.'<br><br><b>Código de erro:</b> 23', 'text/html');
                            });  
                           
                           //Registra reenvio automatico
                           $verificacao_Reenvio = ReenvioAutomatico::where([['encaminhamentos_id', '=', $ultimoEncaminhamento->idEncaminhamentoBacenWebService]])->first();    
                           if(empty($verificacao_Reenvio)){

                                ReenvioAutomatico::create([ 'encaminhamentos_id' => $ultimoEncaminhamento->idEncaminhamentoBacenWebService, 'resposta' => $_POST['respostaAPP'],  'arquivo1' => $fileContent, 'arquivo2' => $fileContent2, 'arquivo3' => $fileContent3, 'tentativas' => 0 , 'sucesso' => 0, 'msg' => 'Erro ao tentar enviar resposta ao WebService RDR BACEN: '.$e->getMessage() , 'instituicaofinanceira_idbacenwebservice' => $InstituicaoFinanceira_DEMANDA->idBacenWebService , 'zendesk_ticket_id' =>  $_POST['app_ticket_id'] , 'fileName1' => $fileName, 'fileName2' => $fileName2 , 'fileName3' => $fileName3 , 'app_agente_id' => $_POST['app_agente_id']   ]); 

                                
                                //Cria comentario - reenvio
                                try{ 
                                        
                                    $body = new \StdClass();
                                    $body->ticket = new \StdClass();                                                                                                
                                    $body->ticket->comment = new \StdClass();
                                    $body->ticket->comment->body = "O envio ao Bacen falhou, uma nova tentativa automática será executada em 15 minutos. \n Nenhuma ação é necessária"; 
                                    $body->ticket->comment->public = false;
                                    $body->ticket->comment->author_id = $_POST['app_agente_id'];
                                    

                                    $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'.$_POST['app_ticket_id']   , [
                                        'headers' => [
                                            'Accept' => 'application/json',
                                            'Content-Type' => 'application/json',
                                        ],
                                        'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                        'body' => json_encode($body) 
                                    ]);

                                    //Obtem id desse usuario recem criado
                                    $createdComentZendesk = $res4->getBody(); 
                                    $createdComentZendeskTratado = json_decode((string) $createdComentZendesk);    
                                
                                }catch(\Exception $e){
                                                                
                                                                    
                                }  
                           
                        }

                           return response()->json([
                               'error' => 'Erro ao tentar enviar resposta ao WebService RDR BACEN. '.$e->getMessage(),
                               'message' => 'Erro ao tentar enviar resposta ao WebService RDR BACEN. '.$e->getMessage()
                           ]);
                           
                        }


                        // SUCESOO !!!!! ******
                        // SUCESOO !!!!! ******
                        // SUCESOO !!!!! ******
                        if($resposta_responderBase64Anexos){

                        
                        //REGISTRA NO MYSQL       
                        //REGISTRA NO MYSQL    
                        $AnexoZendeskCriado = AnexosZendesk::create(['encaminhamentos_id' => $ultimoEncaminhamento->id, 'resposta' => $_POST['respostaAPP'], 'arquivo1' => $fileContent, 'arquivo1_mimetype' => $fileType, 'arquivo2' => $fileContent2, 'arquivo2_mimetype' => $fileType2, 'arquivo3' => $fileContent3, 'arquivo3_mimetype' => $fileType3 ]); 

                        
                        //ZENDESK
                        //ZENDESK
                        //Criar comentário no zendesk c/ anexo(s) , para este ticket $_POST['app_ticket_id'] e alterar situação do ticket
                            
                            //Passo 1 - Faz upload dos anexos no zendesk
                            try{ 
                                //Uploads Anexo 1 , Anexo 2 e Anexo 3                                                                

                                //Anexo 1
                                $token_anexo_1 = '';
                                $res1 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/uploads'   , [
                                    'headers' => [
                                        'Accept' => 'application/binary',
                                        'Content-Type' => 'application/binary',
                                    ],
                                    'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                    'body' => $fileContent,
                                    'query' => ['filename' => $fileName] //parâmetros 
                                ]);  

                                //Obtem token desse arquivo recem criado no zendesk
                                $createdArquivoZendesk = $res1->getBody(); 
                                $createdArquivoZendeskTratado = json_decode((string) $createdArquivoZendesk);    
                                
                                //******************************* */
                                // ou $createdArquivoZendeskTratado->upload->token
                                $token_anexo_1 = $createdArquivoZendeskTratado->upload->token; 
                                
                                //Anexo 2 (opcional)
                                $token_anexo_2 = '';
                                if(array_key_exists('file2', $_FILES) && $_FILES['file2']['name'] && $_FILES['file2']['type']){

                                    //Anexo 2                                    
                                    $res2 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/uploads'   , [
                                        'headers' => [
                                            'Accept' => 'application/binary',
                                            'Content-Type' => 'application/binary',
                                        ],
                                        'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                        'body' => $fileContent2,
                                        'query' => ['filename' => $fileName2] //parâmetros 
                                    ]);  

                                    //Obtem token desse arquivo recem criado no zendesk
                                    $createdArquivoZendesk2 = $res2->getBody(); 
                                    $createdArquivoZendeskTratado2 = json_decode((string) $createdArquivoZendesk2);    
                                                                
                                    $token_anexo_2 = $createdArquivoZendeskTratado2->upload->token; 

                                }                                

                                //Anexo 3 (opcional)
                                $token_anexo_3 = '';
                                if(array_key_exists('file3', $_FILES) && $_FILES['file3']['name'] && $_FILES['file3']['type']){

                                    //Anexo 3                                    
                                    $res3 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/uploads'   , [
                                        'headers' => [
                                            'Accept' => 'application/binary',
                                            'Content-Type' => 'application/binary',
                                        ],
                                        'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                        'body' => $fileContent3,
                                        'query' => ['filename' => $fileName3] //parâmetros 
                                    ]);  

                                    //Obtem token desse arquivo recem criado no zendesk
                                    $createdArquivoZendesk3 = $res3->getBody(); 
                                    $createdArquivoZendeskTratado3 = json_decode((string) $createdArquivoZendesk3);    
                                                                
                                    $token_anexo_3 = $createdArquivoZendeskTratado3->upload->token;                   

                                }

                            }catch(\Exception $e){ 
                                                                
                                //LOG ERRO: 'Não foi possivel fazer upload do(s) arquivo(s) relacionados a resposta-demanda no Zendesk'
                                LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel fazer upload do(s) arquivo(s) relacionados a resposta-demanda no Zendesk: '.$e->getMessage(), 'codigo' => 20, 'demandas_id' => $demanda->id  ]);    


                                
                                                                
                            }  


                            //Passo 2 Cria comentário no Zendesk com anexos do passo 1)
                            //Passo 2
                            try{ 
                                
                                $body = new \StdClass();
                                $body->ticket = new \StdClass();                                                                                                
                                $body->ticket->comment = new \StdClass();
                                $body->ticket->comment->body = "Resposta enviada ao BACEN \n".$_POST['respostaAPP']; 
                                $body->ticket->comment->public = false;
                                $body->ticket->comment->author_id = $_POST['app_agente_id'];
                                $body->ticket->status = "solved";      

                                $lista_de_tokens = [];
                                if($token_anexo_1){
                                    $lista_de_tokens[] = $token_anexo_1;
                                }
                                if($token_anexo_2){
                                    $lista_de_tokens[] = $token_anexo_2;
                                }
                                if($token_anexo_3){
                                    $lista_de_tokens[] = $token_anexo_3;
                                }
                                $body->ticket->comment->uploads = $lista_de_tokens;                       
                                

                                
                                

                                
                                //UPDATE no formulário do ticket, campo: FIELD_SITUACAO_NO_BACEN_ID                                                  
                                $body->ticket->ticket_form_id = env('TICKET_FORM_ID');    
                                                            
                                $obj1 = new \StdClass();
                                $obj1->id = env('FIELD_SITUACAO_NO_BACEN_ID');
                                $obj1->value = 'resposta_enviada_ao_bacen';                      

                                //NOVA DEMANDA            
                                //Adicionar a dataCorrente numa variável PHP e enviá-la para o formulário do ticket. Elton criará este novo campo no zendesk                                
                                /*
                                $obj2 = new \StdClass();
                                $obj2->id = 1260827311089;
                                $obj2->value = date('Y-m-d');                      
                                */

                                $body->ticket->custom_fields = [                            
                                    0 => $obj1
                               /* , 1 => $obj2 */                
                                ];                     
                                  

                                $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'.$_POST['app_ticket_id']   , [
                                    'headers' => [
                                        'Accept' => 'application/json',
                                        'Content-Type' => 'application/json',
                                    ],
                                    'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                    'body' => json_encode($body) 
                                ]);

                                //Obtem id desse usuario recem criado
                                $createdComentZendesk = $res4->getBody(); 
                                $createdComentZendeskTratado = json_decode((string) $createdComentZendesk);    
                             
                            }catch(\Exception $e){
                                                                
                                //LOG ERRO: Não foi possivel conectar-se ao zendesk para criar o comentário do ticket (comentário: Responder Demanda)
                                LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel conectar-se ao zendesk para criar o comentário do ticket (comentário: Responder Demanda) :'.$e->getMessage() , 'codigo' => 21, 'demandas_id' => $demanda->id  ]);            
                                
                                
                                //mesmo assim Prossegue e tenta enviar ao BACEN                                
                                
                            }  


                            //registra log de comando
                            LogsComandos::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'duracao' => 0, 'totalDemandasRetornadasRDRWebservice' => 0 ]);

                            //sucesso
                            return response()->json([
                                'error' => false,  
                                'message' => 'Resposta enviada com sucesso ao BACEN'    
                            ]);

                        }else{

                            //LOG DE ERRO: Erro ao tentar responder Demanda
                           LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Erro ao enviar resposta ao BACEN: retorno inválido do BACEN: retorno false', 'codigo' => 24 , 'demandas_id' => $demanda->id  ]);

                           //envia email                           
                           Mail::send(array(), array(), function ($message) use ($to_email){
                            $message->to($to_email)
                            ->subject('Log - integrador')
                            ->from('joao.peixoto.cd@gmail.com')  
                            ->setBody('<b>comando:</b> responderDemanda<br><br>'.'<b>Mensagem:</b> Erro ao enviar resposta ao BACEN: retorno inválido do BACEN: retorno false <br><br><b>Código de erro:</b> 24', 'text/html');
                            });  


                           
                           //Registra reenvio automatico
                           $verificacao_Reenvio = ReenvioAutomatico::where([['encaminhamentos_id', '=', $ultimoEncaminhamento->idEncaminhamentoBacenWebService]])->first();    
                           if(empty($verificacao_Reenvio)){ 
                           
                                ReenvioAutomatico::create([ 'encaminhamentos_id' => $ultimoEncaminhamento->idEncaminhamentoBacenWebService, 'resposta' => $_POST['respostaAPP'], 'arquivo1' => $fileContent, 'arquivo2' => $fileContent2, 'arquivo3' => $fileContent3 , 'tentativas' => 0 , 'sucesso' => 0, 'msg' => 'Erro ao enviar resposta ao BACEN: retorno inválido do BACEN: retorno false'  , 'instituicaofinanceira_idbacenwebservice' => $InstituicaoFinanceira_DEMANDA->idBacenWebService  , 'zendesk_ticket_id' => $_POST['app_ticket_id']  , 'fileName1' => $fileName, 'fileName2' => $fileName2 , 'fileName3' => $fileName3 , 'app_agente_id' => $_POST['app_agente_id']    ]);

                                    //Cria comentario - reenvio
                                    try{ 
                                        
                                        $body = new \StdClass();
                                        $body->ticket = new \StdClass();                                                                                                
                                        $body->ticket->comment = new \StdClass();
                                        $body->ticket->comment->body = "O envio ao Bacen falhou, uma nova tentativa automática será executada em 15 minutos. \n Nenhuma ação é necessária"; 
                                        $body->ticket->comment->public = false; 
                                        $body->ticket->comment->author_id = $_POST['app_agente_id'];
                                        

                                        $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'.$_POST['app_ticket_id']   , [
                                            'headers' => [
                                                'Accept' => 'application/json',
                                                'Content-Type' => 'application/json',
                                            ],
                                            'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                            'body' => json_encode($body) 
                                        ]);

                                        //Obtem id desse usuario recem criado
                                        $createdComentZendesk = $res4->getBody(); 
                                        $createdComentZendeskTratado = json_decode((string) $createdComentZendesk);    
                                    
                                    }catch(\Exception $e){
                                                                    
                                                                        
                                    } 
                            
                            }

                           return response()->json([  
                               'error' => 'Erro ao enviar resposta ao BACEN: retorno inválido do BACEN',
                               'message' => 'Erro ao enviar resposta ao BACEN: retorno inválido do BACEN'
                           ]);
                           

                        }
                              



                        

                    }else{

                        return response()->json([
                            'error' => 'O Ticket já foi respondido!',
                            'message' => 'O Ticket já foi respondido!'
                        ]);
                        

                    }                  

                }else{

                    return response()->json([
                        'error' => 'O Ticket não possui Demanda associada!',
                        'message' => 'O Ticket não possui Demanda associada!'
                    ]);
                    

                }

            

        }else{
            switch($fileError){
                case UPLOAD_ERR_INI_SIZE:   
                    $message = 'Erro: Ocorreu um erro ao tentar fazer upload de um arquivo que excedeu o tamanho permitido.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:  
                    $message = 'Erro: Ocorreu um erro ao tentar fazer upload de um arquivo que excedeu o tamanho permitido.';
                    break;
                case UPLOAD_ERR_PARTIAL:    
                    $message = 'Erro: o upload do arquivo não foi concluído.';
                    break;
                case UPLOAD_ERR_NO_FILE:    
                    $message = 'Erro: nenhum arquivo foi enviado.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR: 
                    $message = 'Erro: servidor não configurado para upload de arquivo.';
                    break;
                case UPLOAD_ERR_CANT_WRITE: 
                    $message= 'Erro: Possível falha ao salvar o arquivo. Falha de escrita.';
                    break;
                case  UPLOAD_ERR_EXTENSION: 
                    $message = 'Erro: o upload do arquivo não foi concluído. Problema com extensão.';
                    break;
                default: $message = 'Erro: o upload do arquivo não foi concluído.';
                        break;
            }

            
            return response()->json([
                'error' => $message,
                'message' => $message     
            ]);
        }

        // return response()->json(['sucesso' => 'Requisicao ao robo MELI com sucesso   223']);
        
        


    }








    //PRAZOOOO
    //PRAZOOOO
    //PRAZOOOO
    public function enviaSolicitacaoPrazoParaBacen(Request $req){


        set_time_limit(240); //240 seg
        ini_set("memory_limit", "128M"); //128M         


        
        //captura requisição POST  vinda do Zendesk APP     
        

        //EMAIL TO **************************
        $to_email = 'joao.peixoto.cd@gmail.com'; 
        //EMAIL TO **************************

        
        //instancia um httpCliente guzzle
        $parametro_SSL = '';
        if(env('AMBIENTE_LOCAL') == 's'){
            $parametro_SSL = ['verify' => false];
        }else{
            $parametro_SSL = []; 
        }
        $HttpClient = new \GuzzleHttp\Client($parametro_SSL);         



        
        //Processa arquivos 1, 2 (opcional) e 3 (opcional)
        try {
            
            //arquivo 1 (obrigatório)
            $fileName = $_FILES['file']['name'];  
            $fileType = $_FILES['file']['type'];
            $fileError = $_FILES['file']['error'];
            $fileContent = file_get_contents($_FILES['file']['tmp_name']);

            
            
        } catch (RuntimeException $e) {


            //LOG DE ERRO: Erro durante o processamento do arquivo. Verifique o tamanho e extensão dos arquivos a serem enviados.
            LogsErros::create(['comando' => 'solicitarPrazoDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Erro durante o processamento do arquivo. Verifique o tamanho e extensão dos arquivos a serem enviados.'.$e->getMessage() , 'codigo' => 339  ]);


            return response()->json([ 
                'error' => $e->getMessage(),
                'message' => $e->getMessage()
            ]);            
            
        }    
        

        if($fileError == UPLOAD_ERR_OK ){ //UPLOAD BEM SUCEDIDO

            //Processa arquivos e solicitacao de prazo  

            
                //consulta demanda associada a esse ticket
                $demanda = Demandas::where([['zendesk_ticket_id', '=',  $_POST['app_ticket_id']]])->first();    
                
                if($demanda){ 

                    //descobre Instituicao dessa demanda
                    //descobre Instituicao dessa demanda
                    $InstituicaoFinanceira_DEMANDA = InstituicaoFinanceira::where([['id', '=', $demanda->instituicao_financeira_id]])->first();
                                        

                    //Verifica se já existe uma solicitação de Prazo ativa
                    $verifica_SolicitacaoProrrogacaoPrazo = SolicitacaoProrrogacaoPrazo::where([['demandas_id', '=', $demanda->id]])->first();                    

                                        
                    if(empty($verifica_SolicitacaoProrrogacaoPrazo)){                                   
                        
                        //Envia arquivo(s) para o RDRBACEN    
                        //************************** */    
                        //************************** */    
                        //************************** */        
                        
                            
                               
                        //Obtém informações de login do WEBSERVICE BACEN
                        $URL_WEBSERVICE_RDR_BACEN = env('URL_WEBSERVICE_RDR_BACEN_ATUALIZAR_DEMANDA');                   
                        
                        $USER_WEBSERVICE_RDR_BACEN = '';
                        $PASSWORD_WEBSERVICE_RDR_BACEN = ''; 
                        //se for MERCADOPAGO.COM REPRESENTACOES LTDA (CNPJ 10573521) , utiliza-se credenciais dela
                        if($InstituicaoFinanceira_DEMANDA->idBacenWebService == 9999382771){ 

                            $USER_WEBSERVICE_RDR_BACEN = env('USER_WEBSERVICE_RDR_BACEN');
                            $PASSWORD_WEBSERVICE_RDR_BACEN = env('PASSWORD_WEBSERVICE_RDR_BACEN');

                        //se for MERCADO CRÉDITO SOCIEDADE DE CRÉDITO, FINANCIAMENTO E INVESTIMENTO S.A. (CNPJ 37679449) , utiliza-se credenciais dela    
                        }else if($InstituicaoFinanceira_DEMANDA->idBacenWebService == 9999227337){

                            $USER_WEBSERVICE_RDR_BACEN = env('USER_WEBSERVICE_RDR_BACEN2');
                            $PASSWORD_WEBSERVICE_RDR_BACEN = env('PASSWORD_WEBSERVICE_RDR_BACEN2');

                        }else{

                            //LOG DE ERRO: Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes
                            LogsErros::create(['comando' => 'solicitarPrazoDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes' , 'codigo' => 129  , 'demandas_id' => $demanda->id   ]);

                            //envia email
                            Mail::send(array(), array(), function ($message) use ($to_email){
                                $message->to($to_email)
                                ->subject('Log - integrador')
                                ->from('joao.peixoto.cd@gmail.com')  
                                ->setBody('<b>comando:</b> solicitarPrazoDemanda<br><br>'.'<b>Mensagem:</b> Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes'.'<br><br><b>Código de erro:</b> 129', 'text/html');
                            });     

                                
                            return response()->json([
                                'error' => 'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes',
                                'message' => 'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes'
                            ]); 
                            

                        }   




                        //Instancia um SoapClient do PHP
                        $optionsSoap = [
                            'soap_version' => SOAP_1_1, 
                            'exceptions' => true,
                            'trace' => 1,  //LIGA DEBUG do SOAP             
                            'login' => $USER_WEBSERVICE_RDR_BACEN,
                            'password' => $PASSWORD_WEBSERVICE_RDR_BACEN,              
                            'connection_timeout' => 60 //60 segundos aguardando o webservice RDR responder                       
                        ];

                        try{
                            
                            $soapClient = new \SoapClient($URL_WEBSERVICE_RDR_BACEN, $optionsSoap);              
                            
                        } catch(\Exception $e){
                                                                
                                //LOG DE ERRO: REGISTRAR INDISPONIBILIDADE DO WEBSERVICE NO NOSSO BD, ou credenciais desativadas/alteradas
                                LogsErros::create(['comando' => 'solicitarPrazoDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas: '.$e->getMessage() , 'codigo' => 22  , 'demandas_id' => $demanda->id  ]);

                                //envia email
                                Mail::send(array(), array(), function ($message) use ($to_email){
                                    $message->to($to_email)
                                    ->subject('Log - integrador')
                                    ->from('joao.peixoto.cd@gmail.com')  
                                    ->setBody('<b>comando:</b> solicitarPrazoDemanda<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN indisponível ou credenciais desativadas/alteradas'.'<br><br><b>Código de erro:</b> 22', 'text/html');
                                });   
                                
                                return response()->json([
                                    'error' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas',
                                    'message' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas'
                                ]); 
                                

                        }


                        //^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        //ENvia resposta ao BACEN
                        //^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        $anexos_BACENN = [];
                        if(array_key_exists('file', $_FILES) && $_FILES['file']['name'] && $_FILES['file']['type']){

                            $objt1 = new \StdClass();
                            $objt1->nomeArquivo = $fileName;
                            $objt1->conteudoBase64 = base64_encode($fileContent);
                            $anexos_BACENN[] = $objt1;
                        }
                        
                        $parametros = [  
                            'prazoSugerido' => $this->formataPrazoSugerido($_POST['prazoSugeridoAPP']),
                            'justificativa' => $_POST['respostaAPP'],    
                            'idInterno' => $demanda->idInternoBacenWebService, 
                            'anexos' => $anexos_BACENN 
                        ];


                        try{
                            $resposta_prorrogacaoAnexos = $soapClient->solicitarProrrogacaoComAnexo($parametros);    
                        } catch(\Exception $e){    
                            
                            
                           //LOG DE ERRO: Erro ao tentar responder Demanda
                           LogsErros::create(['comando' => 'solicitarPrazoDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Erro ao tentar enviar solicitacao de prazo ao WebService RDR BACEN: '.$e->getMessage() , 'codigo' => 23  , 'demandas_id' => $demanda->id   ]);


                           //envia email
                           $e_current_message = $e->getMessage();
                           Mail::send(array(), array(), function ($message) use ($to_email, $e_current_message){
                            $message->to($to_email)
                            ->subject('Log - integrador')
                            ->from('joao.peixoto.cd@gmail.com')  
                            ->setBody('<b>comando:</b> solicitarPrazoDemanda<br><br>'.'<b>Mensagem:</b> Erro ao tentar enviar solicitacao de prazo ao WebService RDR BACEN:'.$e_current_message.'<br><br><b>Código de erro:</b> 23', 'text/html');
                             });   

                           
                           return response()->json([
                               'error' => 'Erro ao tentar enviar solicitacao de prazo ao WebService RDR BACEN'.$e->getMessage(),  
                               'message' => 'Erro ao tentar enviar solicitacao de prazo ao WebService RDR BACEN'.$e->getMessage()
                           ]);
                           
                        }


                        // SUCESOO !!!!! ******
                        // SUCESOO !!!!! ******
                        // SUCESOO !!!!! ******
                        if($resposta_prorrogacaoAnexos){


                            //registra solicitacao na BASE mysql
                            //registra solicitacao na BASE mysql
                            //registra solicitacao na BASE mysql

                            $prazo_sug = $this->formataPrazoSugerido($_POST['prazoSugeridoAPP']);
                            $prazo_sug_exploded = explode('T', $prazo_sug);
                            $prazo_sug_exploded2 = explode('.', $prazo_sug_exploded[1]); 
                            $prazo_sug_MYSQL = $prazo_sug_exploded[0].' '.$prazo_sug_exploded2[0];

                            SolicitacaoProrrogacaoPrazo::create(['demandas_id' => $demanda->id, 'justificativa' => $_POST['justificativaAPP'], 'prazoSugerido' => $prazo_sug_MYSQL, 'utiliza_anexo' => 's' /* , 'arquivo1' => $fileContent */ ]);
                            
                            

                            //Criar comentário no zendesk c/ anexo(s) , para este ticket $_POST['app_ticket_id']
                            
                            //Passo 1 - Faz upload dos anexos no zendesk
                            try{ 
                                //Uploads Anexo 1  

                                //Anexo 1
                                $token_anexo_1 = '';
                                $res1 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/uploads'   , [
                                    'headers' => [
                                        'Accept' => 'application/binary',
                                        'Content-Type' => 'application/binary',
                                    ],
                                    'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                    'body' => $fileContent,
                                    'query' => ['filename' => $fileName] //parâmetros 
                                ]);  

                                //Obtem token desse arquivo recem criado no zendesk
                                $createdArquivoZendesk = $res1->getBody(); 
                                $createdArquivoZendeskTratado = json_decode((string) $createdArquivoZendesk);    
                                
                                //******************************* */
                                // ou $createdArquivoZendeskTratado->upload->token
                                $token_anexo_1 = $createdArquivoZendeskTratado->upload->token; 
                                

                            }catch(\Exception $e){   
                                                                
                                //LOG ERRO: 'Não foi possivel fazer upload do(s) arquivo(s) relacionados a resposta-demanda no Zendesk'
                                LogsErros::create(['comando' => 'solicitarPrazoDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel fazer upload do(s) arquivo(s) relacionados a solicitacao-prazo-demanda no Zendesk: '.$e->getMessage(), 'codigo' => 20, 'demandas_id' => $demanda->id  ]);    


                                //mesmo assim Prossegue e tenta enviar ao BACEN

                                                                
                            } 


                            //Passo 2 Cria comentário no Zendesk com anexos do passo 1)
                            //Passo 2
                            try{ 
                                
                                $body = new \StdClass();
                                $body->ticket = new \StdClass();                                                                                                
                                $body->ticket->comment = new \StdClass();
                                $body->ticket->comment->body = "Solicitação de Prazo feita ao BACEN \n Prazo Sugerido: ".$_POST['prazoSugeridoAPP']." \n".$_POST['justificativaAPP']; 
                                $body->ticket->comment->public = false;
                                $body->ticket->comment->author_id = $_POST['app_agente_id'];
                                
                                $lista_de_tokens = [];
                                if($token_anexo_1){
                                    $lista_de_tokens[] = $token_anexo_1; 
                                }
                                
                                $body->ticket->comment->uploads = $lista_de_tokens;                       
                                

                                //UPDATE no formulário do ticket, ATUALIZA: FIELD_SITUACAO_NO_BACEN_ID
                                $body->ticket->ticket_form_id = env('TICKET_FORM_ID');    
                                                            
                                $obj1 = new \StdClass();
                                $obj1->id = env('FIELD_SITUACAO_NO_BACEN_ID');
                                $obj1->value = 'pendente_if_ac_solicitacao_de_prazo_pendente';

                                $body->ticket->custom_fields = [                            
                                    0 => $obj1               
                                ];  



                                $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'.$_POST['app_ticket_id']   , [
                                    'headers' => [
                                        'Accept' => 'application/json',
                                        'Content-Type' => 'application/json',
                                    ],
                                    'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                    'body' => json_encode($body) 
                                ]);

                                //Obtem id desse usuario recem criado
                                $createdComentZendesk = $res4->getBody(); 
                                $createdComentZendeskTratado = json_decode((string) $createdComentZendesk);    
                             
                            }catch(\Exception $e){
                                                                
                                //LOG ERRO: Não foi possivel conectar-se ao zendesk para criar o comentário do ticket (comentário: Responder Demanda)
                                LogsErros::create(['comando' => 'solicitarPrazoDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel conectar-se ao zendesk para criar o comentário do ticket (comentário: Solicitar Prazo) :'.$e->getMessage() , 'codigo' => 21, 'demandas_id' => $demanda->id  ]);            
                                
                                
                                //mesmo assim Prossegue e tenta enviar ao BACEN                                
                                
                            }  
                                                         
                            

                            //registra log de comando
                            LogsComandos::create(['comando' => 'solicitarPrazoDemanda', 'data' => date('Y-m-d H:i:s'), 'duracao' => 0, 'totalDemandasRetornadasRDRWebservice' => 0 ]);

                            //sucesso
                            return response()->json([
                                'error' => false,  
                                'message' => 'Solicitação de prazo enviada com sucesso ao BACEN'    
                            ]);

                        }else{

                            //LOG DE ERRO: Erro ao tentar solicitar prazo Demanda
                           LogsErros::create(['comando' => 'solicitarPrazoDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Erro ao enviar solicitação de prazo ao BACEN: retorno inválido do BACEN: retorno false', 'codigo' => 24  , 'demandas_id' => $demanda->id  ]);

                           
                           //envia email                           
                           Mail::send(array(), array(), function ($message) use ($to_email){
                            $message->to($to_email)
                            ->subject('Log - integrador')
                            ->from('joao.peixoto.cd@gmail.com')  
                            ->setBody('<b>comando:</b> solicitarPrazoDemanda<br><br>'.'<b>Mensagem:</b> Erro ao enviar solicitação de prazo ao BACEN: retorno inválido do BACEN: retorno false <br><br><b>Código de erro:</b> 24', 'text/html');
                             });   


                           return response()->json([
                               'error' => 'Erro ao enviar solicitacao de prazo ao BACEN: retorno inválido do BACEN',
                               'message' => 'Erro ao enviar solicitação de prazo ao BACEN: retorno inválido do BACEN'
                           ]);
                           

                        }
                              



                        

                    }else{

                        return response()->json([
                            'error' => 'O Prazo já foi solicitado!',
                            'message' => 'O Prazo já foi solicitado!'
                        ]);
                        

                    }                  

                }else{

                    return response()->json([
                        'error' => 'O Ticket não possui Demanda associada!',
                        'message' => 'O Ticket não possui Demanda associada!'
                    ]);
                    

                }

            

        }else{
            switch($fileError){
                case UPLOAD_ERR_INI_SIZE:   
                    $message = 'Erro: Ocorreu um erro ao tentar fazer upload de um arquivo que excedeu o tamanho permitido.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:  
                    $message = 'Erro: Ocorreu um erro ao tentar fazer upload de um arquivo que excedeu o tamanho permitido.';
                    break;
                case UPLOAD_ERR_PARTIAL:    
                    $message = 'Erro: o upload do arquivo não foi concluído.';
                    break;
                case UPLOAD_ERR_NO_FILE:    
                    $message = 'Erro: nenhum arquivo foi enviado.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR: 
                    $message = 'Erro: servidor não configurado para upload de arquivo.';
                    break;
                case UPLOAD_ERR_CANT_WRITE: 
                    $message= 'Erro: Possível falha ao salvar o arquivo. Falha de escrita.';
                    break;
                case  UPLOAD_ERR_EXTENSION: 
                    $message = 'Erro: o upload do arquivo não foi concluído. Problema com extensão.';
                    break;
                default: $message = 'Erro: o upload do arquivo não foi concluído.';
                        break;
            }
            return response()->json([
                'error' => $message,
                'message' => $message     
            ]);
        }

        // return response()->json(['sucesso' => 'Requisicao ao robo MELI com sucesso   223']);
        



    }   



    public function formataPrazoSugerido($prazo){

        $p_explodido = explode('/',  $prazo);

        //se o usuário preencheu errado: caso 1 (coloca uma sugestao de 30 dias adiante)
        if(!is_array($p_explodido)){
            return date('Y-m-d',strtotime('+30 day')).'T04:00:00.000';
        }

        //se o usuário preencheu errado: caso 2 (coloca uma sugestao de 30 dias adiante)
        if(count($p_explodido) != 3){
            return date('Y-m-d',strtotime('+30 day')).'T04:00:00.000';
        }

        //checka se a data é valida:  (coloca uma sugestao de 30 dias adiante)       
        if(!checkdate($p_explodido[1], $p_explodido[0], $p_explodido[2])){
            return date('Y-m-d',strtotime('+30 day')).'T04:00:00.000';
        }


        //converte para formato norte americano para ENVIAR ao BACEN
        return  $p_explodido[2].'-'.$p_explodido[1].'-'.$p_explodido[0].'T04:00:00.000';


    }



}
