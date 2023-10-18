<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

//models Utilizadas
use App\Demandas;
use App\Encaminhamentos;
use App\AnexosZendesk;
use App\LogsErros;
use App\LogsComandos;  
use App\SolicitacaoProrrogacaoPrazo; 
use App\InstituicaoFinanceira;           
use App\ReenvioAutomatico;


class ReenvioAutomaticoIntegrador extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reenvioautomaticointegrador';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       

        //instancia um httpCliente guzzle
        $parametro_SSL = '';
        if(env('AMBIENTE_LOCAL') == 's'){
            $parametro_SSL = ['verify' => false];
        }else{
            $parametro_SSL = []; 
        }
        $HttpClient = new \GuzzleHttp\Client($parametro_SSL);      


        $n_tentativas = 3;


        //consulta reenvios pendentes
        $reenvios = ReenvioAutomatico::where([
            ['tentativas', '<', $n_tentativas],
            ['sucesso', '=', 0]
        ])->orderBy('id', 'ASC')->get();                    

        if($reenvios){
            foreach($reenvios as $reenvio){
               

                


                //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

                //descobre o encaminhamentos_id a partir de app_ticket_id 
                //consulta demanda associada a esse ticket
                $demanda = Demandas::where([['zendesk_ticket_id', '=',  $reenvio->zendesk_ticket_id ]])->first();    
                
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
                            //LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes' , 'codigo' => 129 ]);
                                                        
                            
                            ReenvioAutomatico::where([['id', '=', $reenvio->id ]])->update(['tentativas' => ($reenvio->tentativas + 1) , 'msg' => 'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes' ]);       

                            //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><
                            //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><                            
                                
                            $body = new \StdClass();
                            $body->ticket = new \StdClass();                                                                                                
                            $body->ticket->comment = new \StdClass();
                            $body->ticket->comment->body = "Tentativa ". ($reenvio->tentativas + 1) ." de reenvio automático falhou: \n".'Instituicao Financeira da demanda inexistente / credenciais dessa instituicao ausentes'; 
                            $body->ticket->comment->public = false;
                                                                                                                            

                            $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'. $reenvio->zendesk_ticket_id  , [
                                'headers' => [
                                    'Accept' => 'application/json',
                                    'Content-Type' => 'application/json',
                                ],
                                'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                'body' => json_encode($body) 
                            ]);

                            //
                            $createdComentZendeskTentativa = $res4->getBody(); 
                            $createdComentZendeskTratadoTentativa = json_decode((string) $createdComentZendeskTentativa);    
                            
                        
                            


                            //próxima iteração
                            continue;                            

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
                                //LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas: '.$e->getMessage() , 'codigo' => 22 ]);

                               
                            ReenvioAutomatico::where([['id', '=', $reenvio->id ]])->update(['tentativas' => ($reenvio->tentativas + 1) , 'msg' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas: '.$e->getMessage() ]);       


                            //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><
                            //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><                            
                                
                            $body = new \StdClass();
                            $body->ticket = new \StdClass();                                                                                                
                            $body->ticket->comment = new \StdClass();
                            $body->ticket->comment->body = "Tentativa ". ($reenvio->tentativas + 1) ." de reenvio automático falhou: \n".'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas'; 
                            $body->ticket->comment->public = false;
                                                                                                                            

                            $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'. $reenvio->zendesk_ticket_id  , [
                                'headers' => [
                                    'Accept' => 'application/json',
                                    'Content-Type' => 'application/json',
                                ],
                                'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                'body' => json_encode($body) 
                            ]);

                            //
                            $createdComentZendeskTentativa = $res4->getBody(); 
                            $createdComentZendeskTratadoTentativa = json_decode((string) $createdComentZendeskTentativa);    
                            
                        


                            //próxima iteração
                            continue;         

                        }


                        //^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        //ENvia resposta ao BACEN
                        //^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        $anexos_BACENN = [];
                        if($reenvio->arquivo1 && $reenvio->fileName1){

                            $objt1 = new \StdClass();
                            $objt1->nomeArquivo = $reenvio->fileName1;
                            $objt1->conteudoBase64 = base64_encode($reenvio->arquivo1);
                            $anexos_BACENN[] = $objt1;
                        }
                        if($reenvio->arquivo2 && $reenvio->fileName2){

                            $objt2 = new \StdClass();
                            $objt2->nomeArquivo = $reenvio->fileName2;
                            $objt2->conteudoBase64 = base64_encode($reenvio->arquivo2);
                            $anexos_BACENN[] = $objt2;
                        }
                        if($reenvio->arquivo3 && $reenvio->fileName3){     

                            $objt3 = new \StdClass();
                            $objt3->nomeArquivo = $reenvio->fileName3;
                            $objt3->conteudoBase64 = base64_encode($reenvio->arquivo3);
                            $anexos_BACENN[] = $objt3;
                        }
                        $parametros = [  
                            'resposta' => $reenvio->resposta,                              
                            'idEncaminhamento' => $reenvio->encaminhamentos_id, 
                            'anexos' => $anexos_BACENN 
                        ];


                        try{
                            $resposta_responderBase64Anexos = $soapClient->responderBase64Anexos($parametros);    
                        } catch(\Exception $e){    
                            
                            
                           //LOG DE ERRO: Erro ao tentar responder Demanda
                           //LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Erro ao tentar enviar resposta ao WebService RDR BACEN: '.$e->getMessage() , 'codigo' => 23 ]);

                           
                           ReenvioAutomatico::where([['id', '=', $reenvio->id ]])->update(['tentativas' => ($reenvio->tentativas + 1) , 'msg' => 'Erro ao tentar enviar resposta ao WebService RDR BACEN: '.$e->getMessage() ]);       



                           //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><
                           //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><                            
                                
                            $body = new \StdClass();
                            $body->ticket = new \StdClass();                                                                                                
                            $body->ticket->comment = new \StdClass();
                            $body->ticket->comment->body = "Tentativa ". ($reenvio->tentativas + 1) ." de reenvio automático falhou: \n".'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas'; 
                            $body->ticket->comment->public = false; 
                                                                                                                            

                            $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'. $reenvio->zendesk_ticket_id  , [
                                'headers' => [
                                    'Accept' => 'application/json',
                                    'Content-Type' => 'application/json',
                                ],
                                'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                'body' => json_encode($body) 
                            ]);

                            //
                            $createdComentZendeskTentativa = $res4->getBody(); 
                            $createdComentZendeskTratadoTentativa = json_decode((string) $createdComentZendeskTentativa);    
                            
                        


                           //próxima iteração
                           continue;                                   
                           
                        }


                        // SUCESOO !!!!! ******
                        // SUCESOO !!!!! ******
                        // SUCESOO !!!!! ******
                        if($resposta_responderBase64Anexos){

                        
                        //REGISTRA NO MYSQL       
                        //REGISTRA NO MYSQL    
                        $AnexoZendeskCriado = AnexosZendesk::create(['encaminhamentos_id' => $ultimoEncaminhamento->id, 'resposta' => $reenvio->resposta , 'arquivo1' => $reenvio->arquivo1 , 'arquivo2' => $reenvio->arquivo2, 'arquivo3' => $reenvio->arquivo3 ]); 

                        
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
                                    'body' => $reenvio->arquivo1,
                                    'query' => ['filename' => $reenvio->fileName1] //parâmetros 
                                ]);  

                                //Obtem token desse arquivo recem criado no zendesk
                                $createdArquivoZendesk = $res1->getBody(); 
                                $createdArquivoZendeskTratado = json_decode((string) $createdArquivoZendesk);    
                                
                                //******************************* */
                                // ou $createdArquivoZendeskTratado->upload->token
                                $token_anexo_1 = $createdArquivoZendeskTratado->upload->token; 
                                
                                //Anexo 2 (opcional)
                                $token_anexo_2 = '';
                                if($reenvio->arquivo2 && $reenvio->fileName2){

                                    //Anexo 2                                    
                                    $res2 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/uploads'   , [
                                        'headers' => [
                                            'Accept' => 'application/binary',
                                            'Content-Type' => 'application/binary',
                                        ],
                                        'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                        'body' => $reenvio->arquivo2,
                                        'query' => ['filename' => $reenvio->fileName2] //parâmetros 
                                    ]);  

                                    //Obtem token desse arquivo recem criado no zendesk
                                    $createdArquivoZendesk2 = $res2->getBody(); 
                                    $createdArquivoZendeskTratado2 = json_decode((string) $createdArquivoZendesk2);    
                                                                
                                    $token_anexo_2 = $createdArquivoZendeskTratado2->upload->token; 

                                }                                

                                //Anexo 3 (opcional)
                                $token_anexo_3 = '';
                                if($reenvio->arquivo3 && $reenvio->fileName3){

                                    //Anexo 3                                    
                                    $res3 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/uploads'   , [
                                        'headers' => [
                                            'Accept' => 'application/binary',
                                            'Content-Type' => 'application/binary',
                                        ],
                                        'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                        'body' => $reenvio->arquivo3,
                                        'query' => ['filename' => $reenvio->fileName3] //parâmetros 
                                    ]);  

                                    //Obtem token desse arquivo recem criado no zendesk
                                    $createdArquivoZendesk3 = $res3->getBody(); 
                                    $createdArquivoZendeskTratado3 = json_decode((string) $createdArquivoZendesk3);    
                                                                
                                    $token_anexo_3 = $createdArquivoZendeskTratado3->upload->token;                   

                                }

                            }catch(\Exception $e){ 
                                                                
                                //LOG ERRO: 'Não foi possivel fazer upload do(s) arquivo(s) relacionados a resposta-demanda no Zendesk'
                                LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => '111.Não foi possivel fazer upload do(s) arquivo(s) relacionados a resposta-demanda no Zendesk: '.$e->getMessage(), 'codigo' => 20, 'demandas_id' => $demanda->id  ]);    
                             
                                                                
                            }  


                            //Passo 2 Cria comentário no Zendesk com anexos do passo 1)
                            //Passo 2
                            try{ 
                                
                                $body = new \StdClass();
                                $body->ticket = new \StdClass();                                                                                                
                                $body->ticket->comment = new \StdClass();
                                $body->ticket->comment->body = "Resposta enviada ao BACEN \n".$reenvio->resposta; 
                                $body->ticket->comment->public = false;
                                $body->ticket->comment->author_id = $reenvio->app_agente_id;
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

                                $body->ticket->custom_fields = [                            
                                    0 => $obj1               
                                ];      
                                  

                                $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'. $reenvio->zendesk_ticket_id  , [
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
                                LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => '111.Não foi possivel conectar-se ao zendesk para criar o comentário do ticket (comentário: Responder Demanda) :'.$e->getMessage() , 'codigo' => 21, 'demandas_id' => $demanda->id  ]);            
                                
                                
                                //mesmo assim Prossegue e tenta enviar ao BACEN                                
                                
                            }  


                            //registra log de comando
                            LogsComandos::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'duracao' => 0, 'totalDemandasRetornadasRDRWebservice' => 0 ]);


                           ReenvioAutomatico::where([['id', '=', $reenvio->id ]])->update(['tentativas' => ($reenvio->tentativas + 1) , 'sucesso' => 1, 'msg' => 'Resposta Enviada com sucesso ao BACEN' ]);         

                           //próxima iteração
                           continue;                                   


                        }else{

                            //LOG DE ERRO: Erro ao tentar responder Demanda
                           //LogsErros::create(['comando' => 'responderDemanda', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Erro ao enviar resposta ao BACEN: retorno inválido do BACEN: retorno false', 'codigo' => 24 ]);

                           //se nao conseguir enviar (ROLLBACK) , apaga registro de anexos_zendesk criado no passo acima (para permitir enviar novamente)
                           //AnexosZendesk::where([['id', '=', $AnexoZendeskCriado->id]])->delete();                                                      

                           
                           
                           ReenvioAutomatico::where([['id', '=', $reenvio->id ]])->update(['tentativas' => ($reenvio->tentativas + 1) , 'msg' => 'Erro ao enviar resposta ao BACEN: retorno inválido do BACEN: retorno false'  ]);       

                           //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><
                           //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><                            
                                
                            $body = new \StdClass();
                            $body->ticket = new \StdClass();                                                                                                
                            $body->ticket->comment = new \StdClass();
                            $body->ticket->comment->body = "Tentativa ". ($reenvio->tentativas + 1) ." de reenvio automático falhou: \n".'Erro ao enviar resposta ao BACEN: retorno inválido do BACEN: retorno false'; 
                            $body->ticket->comment->public = false;
                                                                                                                            

                            $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'. $reenvio->zendesk_ticket_id  , [
                                'headers' => [
                                    'Accept' => 'application/json',
                                    'Content-Type' => 'application/json',
                                ],
                                'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                'body' => json_encode($body) 
                            ]);

                            //
                            $createdComentZendeskTentativa = $res4->getBody(); 
                            $createdComentZendeskTratadoTentativa = json_decode((string) $createdComentZendeskTentativa);    
                            
                        

                           //próxima iteração
                           continue;                                   
                           
                           

                        }
                              



                        

                    }else{

                        ReenvioAutomatico::where([['id', '=', $reenvio->id ]])->update(['tentativas' => ($reenvio->tentativas + 1) , 'msg' => 'O Ticket já foi respondido!'  ]);       

                        //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><
                        //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><                            
                                
                           $body = new \StdClass();
                           $body->ticket = new \StdClass();                                                                                                
                           $body->ticket->comment = new \StdClass();
                           $body->ticket->comment->body = "Tentativa ". ($reenvio->tentativas + 1) ." de reenvio automático falhou: \n".'O Ticket já foi respondido!'; 
                           $body->ticket->comment->public = false;
                                                                                                                           

                           $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'. $reenvio->zendesk_ticket_id  , [
                               'headers' => [
                                   'Accept' => 'application/json',
                                   'Content-Type' => 'application/json',
                               ],
                               'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                               'body' => json_encode($body) 
                           ]);

                           //
                           $createdComentZendeskTentativa = $res4->getBody(); 
                           $createdComentZendeskTratadoTentativa = json_decode((string) $createdComentZendeskTentativa);    
                          


                        //próxima iteração
                        continue;                                   
                                                

                    }                  

                }else{

                    ReenvioAutomatico::where([['id', '=', $reenvio->id ]])->update(['tentativas' => ($reenvio->tentativas + 1) , 'msg' => 'O Ticket não possui Demanda associada!'  ]);       


                    //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><
                        //POSTA NO ZENDESK ESTA TENTATIVA QUE FALHOU <>><><                              
                                
                        $body = new \StdClass();
                        $body->ticket = new \StdClass();                                                                                                
                        $body->ticket->comment = new \StdClass();
                        $body->ticket->comment->body = "Tentativa ". ($reenvio->tentativas + 1) ." de reenvio automático falhou: \n".'O Ticket não possui Demanda associada!'; 
                        $body->ticket->comment->public = false;
                                                                                                                        

                        $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'. $reenvio->zendesk_ticket_id  , [
                            'headers' => [
                                'Accept' => 'application/json',
                                'Content-Type' => 'application/json',
                            ],
                            'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                            'body' => json_encode($body) 
                        ]);

                        //
                        $createdComentZendeskTentativa = $res4->getBody(); 
                        $createdComentZendeskTratadoTentativa = json_decode((string) $createdComentZendeskTentativa);    
                          

                    


                    //próxima iteração
                    continue;                                   
                        
                                        

                }

                //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&



            }
        }

    }
}
