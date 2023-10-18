<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

//models Utilizadas
use App\InstituicaoFinanceira;
use App\SituacaoBacen;
use App\CanalAtendimento;
use App\Motivo;
use App\TipoRegistro;
use App\Demandas;
use App\Cidadao; 
use App\Encaminhamentos;
use App\LogsErros;
use App\LogsComandos;         

use Mail;


class ConsultarDemandasReinteracao extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consultardemandasreinteracao';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta reinteração de demandas no BACEN WebService na data corrente e armazena na BASE MYSQL do Robô, depois envia para o Zendesk';

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
        //--- seta timezone    
        date_default_timezone_set('America/Sao_Paulo');

        $time_start = microtime(true);

        //EMAIL TO **************************
        $to_email = 'joao.peixoto.cd@gmail.com'; 
        //EMAIL TO **************************

         
        
        //&%^*&%&^%&^%&^$%^$^%$^%(*&(*&(*^!@)@()))
        $conexoes_credenciais_Instituicoes = [
            0 => ['user'=> env('USER_WEBSERVICE_RDR_BACEN'), 'password' => env('PASSWORD_WEBSERVICE_RDR_BACEN')],
            1 => ['user'=> env('USER_WEBSERVICE_RDR_BACEN2'), 'password' => env('PASSWORD_WEBSERVICE_RDR_BACEN2')]
        ];


        foreach($conexoes_credenciais_Instituicoes as $key_Inst => $value_Inst){ 
                        


        //Obtém informações de login do WEBSERVICE BACEN
        $URL_WEBSERVICE_RDR_BACEN = env('URL_WEBSERVICE_RDR_BACEN');
        $USER_WEBSERVICE_RDR_BACEN = $value_Inst['user'];
        $PASSWORD_WEBSERVICE_RDR_BACEN = $value_Inst['password'];


        //Instituição corrente ********************
        $instituicao_corrente_value = $value_Inst['user'];
        //Instituição corrente ********************



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
                
                //echo ('Soap Exception: ' . $e->getMessage());  

                //LOG DE ERRO: REGISTRAR INDISPONIBILIDADE DO WEBSERVICE NO NOSSO BD, ou credenciais desativadas/alteradas
                LogsErros::create(['comando' => 'consultardemandasreinteracao', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas', 'codigo' => 1 ]);

                //envia email
                Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value){
                    $message->to($to_email)
                      ->subject('Log - integrador')
                      ->from('joao.peixoto.cd@gmail.com')  
                      ->setBody('<b>comando:</b> consultardemandasreinteracao<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN indisponível ou credenciais desativadas/alteradas'.'<br><br><b>Código de erro:</b> 1'.'<br><b>Instituição:</b>'.$instituicao_corrente_value, 'text/html');
                });     


                //Finaliza comando
                return;
        }


            
        //requisita demandas reinteracao
        //Algoritmo
        
        //Total de demandas "reinteracao" na data corrente
        $parametros = [
            'dataInicio' => $this->getYesterday().'T00:00:00.000', //Alterar para data corrente do servidor ***********!!!!!!!!
            'dataFim' => $this->getDataCorrente().'T23:59:00.000', //Alterar para data corrente do servidor  *************!!!!!!!!
            'tipoConsulta' => "A", //Data de disponibilização
            'situacao' => 16  //Pendente IF/AC                    
        ];

        try{
            $totalDemandas = $soapClient->getTotalDemandas($parametros);    
        } catch(\Exception $e){    
            
            //echo ('Soap Exception: ' . $e->getMessage());

            //LOG ERRO: REGISTRAR INDISPONIBILIDADE DO WEBSERVICE NO NOSSO BD, ou credenciais desativadas/alteradas
            LogsErros::create(['comando' => 'consultardemandasreinteracao', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas ou método getTotalDemandas indisponível', 'codigo' => 2 ]);

            //envia email
            Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value){
                $message->to($to_email)
                  ->subject('Log - integrador')
                  ->from('joao.peixoto.cd@gmail.com')  
                  ->setBody('<b>comando:</b> consultardemandasreinteracao<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN indisponível ou credenciais desativadas/alteradas ou método getTotalDemandas indisponível'.'<br><br><b>Código de erro:</b> 2'.'<br><b>Instituição:</b>'.$instituicao_corrente_value, 'text/html');
            });     

            //Finaliza comando
            return;
        }
        
        //Se não retornou um inteiro em $totalDemandas->return 
        if(!is_object($totalDemandas) ||  (is_object($totalDemandas) && !property_exists($totalDemandas, "return")) || (is_object($totalDemandas) && property_exists($totalDemandas, "return") && !is_int($totalDemandas->return))){
            
            //LOG ERRO: registra no BD retorno inválido referente ao número de demandas
            LogsErros::create(['comando' => 'consultardemandasreinteracao', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN retornou um valor inválido para o número de demandas reinteracao que foi consultado', 'codigo' => 3 ]);  

            //envia email
            Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value){
                $message->to($to_email)
                  ->subject('Log - integrador')
                  ->from('joao.peixoto.cd@gmail.com')  
                  ->setBody('<b>comando:</b> consultardemandasreinteracao<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN retornou um valor inválido para o número de demandas reinteração que foi consultado'.'<br><br><b>Código de erro:</b> 3'.'<br><b>Instituição:</b>'.$instituicao_corrente_value, 'text/html');
            });     

            //Finaliza comando
            return;
        }

        //calcula paginação
        $npaginas = 0;

        if($totalDemandas->return == 0){ //Nenhuma nova demanda reinteracao

            //LOG ERRO nenhuma nova demanda reinteracao
            LogsErros::create(['comando' => 'consultardemandasreinteracao', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Ocorrência: nenhuma  demanda reinteração foi encontrada', 'codigo' => 4 ]);   

            //vai Para próxima instituição
            continue; //&%^*&%&^%&^%&^$%^$^%$^%(*&(*&(*^!@)@()))

        }else if($totalDemandas->return > 0 && $totalDemandas->return < 30){
           
            $npaginas = 1;

        }else if($totalDemandas->return >= 30){

            $npaginas = intdiv($totalDemandas->return, 30);
             if($totalDemandas->return % 30 > 0){
                 ++$npaginas;
             }
        } 

        
        //percorre páginas
        //paginas variam de 0 até n-1
        for($i = 0 ; $i < $npaginas ; ++$i){

            //requisita Demandas dessa página
            $parametros = [
                'dataInicio' => $this->getYesterday().'T00:00:00.000', //Alterar para data corrente do servidor ***********!!!!!!!
                'dataFim' => $this->getDataCorrente().'T23:59:00.000', //Alterar para data corrente do servidor  *************!!!!!!!      
                'tipoConsulta' => "A", //Data de disponibilização
                'situacao' => 16,  //Pendente IF/AC                  
                'pagina' => $i
            ];
            $demandas = $soapClient->getDemandas($parametros); 

            //percorre demandas retornadas 
            if(is_object($demandas) && property_exists($demandas, "return")){

                //se retornou apenas uma demanda, transforma em array 
                if(is_object($demandas->return)){
                    $demandas->return = [ 0 => $demandas->return ];
                }

                if(is_array($demandas->return)){
                    foreach($demandas->return as $key_demanda => $demanda){                     
                        if(is_object($demanda)){


                            //dados Gerais dessa demanda 
                            //var_dump($demanda);                               
                            //echo "\n";
                            //echo "\n";


                            //requisita Detalhes dessa demanda
                            $parametros = [
                                'idInterno' => $demanda->idInterno
                            ];                            
                            $demanda_detalhes = $soapClient->getDemanda($parametros);

                            
                                                                   


                            //Salvar dados no BD
                                //dados minimos para salavr no BD
                                    //se não tem dados mínimos:  continue;
                            
                            if(!is_object($demanda_detalhes) || (is_object($demanda_detalhes) && !property_exists($demanda_detalhes, "return")) || !is_int($demanda_detalhes->return->idInterno) || !is_int($demanda_detalhes->return->idEncaminhamento)){
                                   
                                   //LOG ERRO: Registrar falha de leitura de demanda
                                   LogsErros::create(['comando' => 'consultardemandasreinteracao', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Falha na leitura de demanda do WebServive BACEN: Demanda com valores obrigatórios ausentes', 'codigo' => 5 ]);  
                                   
                                   //envia email
                                   Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value){
                                    $message->to($to_email)
                                    ->subject('Log - integrador')
                                    ->from('joao.peixoto.cd@gmail.com')  
                                    ->setBody('<b>comando:</b> consultardemandasreinteracao<br><br>'.'<b>Mensagem:</b> Falha na leitura de demanda do WebServive BACEN: Demanda com estrutura inválida: Sem idEncaminhamento ou com corpo de dados diferente do formato esperado.'.'<br><br><b>Código de erro:</b> 5'.'<br><b>Instituição:</b>'.$instituicao_corrente_value, 'text/html');
                                    });        


                                   continue;    
                                   
                            }        

                            
                            // ---------------   ATUALIZA APENAS SITUACAO BACEN    ------------------- 
                            //checka se a situação_bacen da demanda existe ou não no BD
                                $situacao_bacen_id = NULL;
                                $situacao_bacen_nome = '';
                            if($demanda_detalhes->return->situacao->id){                                
                                $checka_SituacaoBacen = SituacaoBacen::where([['idBacenWebService', '=', $demanda_detalhes->return->situacao->id]])->first();    
                                if(empty($checka_SituacaoBacen)){  

                                    //insere situação_bacen
                                    $SituacaoBacenInserida = SituacaoBacen::create(['idBacenWebService' => $demanda_detalhes->return->situacao->id, 'descricao' => $demanda_detalhes->return->situacao->descricao, 'alteravelIF' => $demanda_detalhes->return->situacao->alteravelIF]);
                                    $situacao_bacen_id = $SituacaoBacenInserida->id;    
                                    $situacao_bacen_nome = $SituacaoBacenInserida->descricao;    
                                }else{
                                    $situacao_bacen_id = $checka_SituacaoBacen->id;
                                    $situacao_bacen_nome = $checka_SituacaoBacen->descricao;      
                                }  
                            }
                                                                                                             
                            
                            
                            //checka se esta demanda EXISTE na base MYSQL e se o encaminhamento que está chegando é novo
                            //checka se esta demanda EXISTE na base MYSQL e se o encaminhamento que está chegando é novo
                            $checka_DemandaEXISTE = Demandas::where([['idInternoBacenWebService', '=', $demanda_detalhes->return->idInterno]])->first();
                            if(empty($checka_DemandaEXISTE)){
                    
                                //LOG ERRO: demanda reinteração nem existe no zendesk/Robô
                                LogsErros::create(['comando' => 'consultardemandasreinteracao', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Demanda foi reinteração mas não existe no zendesk nem robô meli. Portanto fica presa no robô e não será enviada ao zendesk.', 'codigo' => 70, 'demandas_id' => $demanda_detalhes->return->idInterno ]);    

                                //envia email
                                /*
                                $n_demanda_p_email = (property_exists($demanda_detalhes->return, 'numeroDemanda') && is_int($demanda_detalhes->return->numeroDemanda))?$demanda_detalhes->return->numeroDemanda:'BACEN não informou este dado';
                                Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value, $n_demanda_p_email){
                                    $message->to($to_email)
                                    ->subject('Log - integrador')
                                    ->from('joao.peixoto.cd@gmail.com')  
                                    ->setBody('<b>comando:</b> consultardemandasreinteracao<br><br>'.'<b>Mensagem:</b> Demanda foi reinteração mas não existe no zendesk nem robô meli. Portanto fica presa no robô e não será enviada ao zendesk.'.'<br><br><b>Código de erro:</b> 70'.'<br><b>Instituição:</b>'.$instituicao_corrente_value.' <br><b>Numero Demanda:</b>'.$n_demanda_p_email, 'text/html');
                                }); 
                                */                          


                                continue;
                            }else{

                                //A demanda existente não pode ter os status:  (evitar processamento repetido) 
                                    //'situacao_no_robo' => 'reinteracao_nao_enviada_ao_zendesk'
                                    //'situacao_no_robo' => 'reinteracao_enviada_ao_zendesk'                                
                                if($checka_DemandaEXISTE->situacao_no_robo == 'reinteracao_nao_enviada_ao_zendesk'  /*  || $checka_DemandaEXISTE->situacao_no_robo == 'reinteracao_enviada_ao_zendesk'  */          ){

                                    continue;
                                }
                                

                                
                                //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                                //checka se o idencaminhamento é repetido                                
                                $verifica_encaminhamento_rep = Encaminhamentos::where([['idEncaminhamentoBacenWebService', '=', $demanda_detalhes->return->idEncaminhamento]])->first();
                                
                                if($verifica_encaminhamento_rep){                                    
                                    continue;
                                }


                            } 
                           

                            //tudo ok, demanda existe na base MYSQL e receberá um UPDATE e um novo encaminhamento
                            //CONTINUA ::                            
                            //informações da demanda disponíveis aqui: $checka_DemandaEXISTE

                            //ATUALIZA situacao_bacen_id e situação robô
                            if($situacao_bacen_id){

                                Demandas::where([['id', '=', $checka_DemandaEXISTE->id]])->update(['situacao_bacen_id' => $situacao_bacen_id, 'situacao_no_robo' => 'reinteracao_nao_enviada_ao_zendesk' ]);

                            }                             
                            

                            //// DIAS REITERACAO ///////////////
                            ////calcula valor de dias_reiteracao
                                //consulta ultimo encaminhamento
                                $ultimoEncaminhamento = Encaminhamentos::where([['demandas_id', '=', $checka_DemandaEXISTE->id]])->orderBy('id', 'DESC')->first();                    
                                $ultimo_dias_reiteracao = '';
                                if($ultimoEncaminhamento->dias_reiteracao){
                                    $ultimo_dias_reiteracao = $ultimoEncaminhamento->dias_reiteracao;
                                }else{
                                    $ultimo_dias_reiteracao = 0;
                                }

                                //obtem prazo (YYYY-mm-dd)
                                $prazo_explodidddo = explode('T', $demanda_detalhes->return->prazo);

                                /*
                                //obtem data de disponibilizacao (YYYY-mm-dd)
                                $dataDisponibilizacao_explodiddo = explode('T', $demanda_detalhes->return->dataDisponibilizacao);

                                //calcula prazo - data de disponibilizacao  -  em dias
                                $datetime1 = new \DateTime($dataDisponibilizacao_explodiddo[0]);
                                $datetime2 = new \DateTime($prazo_explodidddo[0]);
                                $difference = $datetime1->diff($datetime2);
                                $diferenca_dias_reiteracao = $difference->days;   
                                */

                                //cálculo final: $diferenca_dias_reiteracao + $ultimo_dias_reiteracao
                                $calculo_final_dias_reiteracao = 0;
                                
                                
                            //Insere Encaminhamento
                            $encaminhamento = Encaminhamentos::create(['demandas_id' => $checka_DemandaEXISTE->id, 'idEncaminhamentoBacenWebService' => $demanda_detalhes->return->idEncaminhamento, 'dias_reiteracao' => $calculo_final_dias_reiteracao ]); 
                            $encaminhamentos_id = $encaminhamento->id;
                            
                            //Insere Anexos BACEN
                            //***** */ 
                                //De acordo com MELI: NÃO HAVERÁ ANEXOS NESTE CASO: REINTERACAO
                            
                            
                            
                                





                            /*****************************************/ 
                            /******************ZENDESK ***************/
                            /*****************************************/

                            
                            //ZENDESK &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                            //ZENDESK &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&                            

                            //ENVIA DADOS PARA ZENDESK via biblioteca https guzzle
                            $parametro_SSL = '';
                            if(env('AMBIENTE_LOCAL') == 's'){
                                $parametro_SSL = ['verify' => false];
                            }else{
                                $parametro_SSL = []; 
                            }
                            $HttpClient = new \GuzzleHttp\Client($parametro_SSL);
                                                        

                            //Atualiza Ticket no Zendesk, criando um comentário sobre o novo status (Reinteração) e atualizando o campo FIELD_STATUS_FINAL_DO_CASO e o campo FIELD_DIAS_REITERACAO                                                 
                            try{ 
                                
                                $body = new \StdClass();
                                $body->ticket = new \StdClass();                                                                                                
                                $body->ticket->comment = new \StdClass();
                                $body->ticket->comment->body = "Demanda com Reiteração";
                                $body->ticket->comment->public = false;
                                $body->ticket->status = "open"; 
                                //$body->ticket->comment->author_id = $_POST['app_agente_id'];
                                

                                //adiciona no comment a descricao do BACEN
                                if($demanda_detalhes->return->descricao){
                                    $ultimo_bloco_hr = '';
                                    $descricao_explodida = explode("________________________________________________________________________________________", $demanda_detalhes->return->descricao); 
                                    if(is_array($descricao_explodida) && count($descricao_explodida) >= 2){
                                        //pega ultimo bloco de <hr>
                                        $ultimo_bloco_hr = $descricao_explodida[count($descricao_explodida)-1];
                                    }
                                    if($ultimo_bloco_hr){
                                        $body->ticket->comment->body .= "\n".$ultimo_bloco_hr;     
                                    }
                                }

                                
                                

                                //UPDATE no formulário do ticket, campo: FIELD_STATUS_FINAL_DO_CASO e FIELD_DIAS_REITERACAO
                                $body->ticket->ticket_form_id = env('TICKET_FORM_ID');    
                                                        
                                $obj0 = new \StdClass();
                                $obj0->id = env('FIELD_SITUACAO_NO_BACEN_ID');
                                $obj0->value = 'pendente_reiteracao';

                                $obj1 = new \StdClass(); 
                                $obj1->id = env('FIELD_DIAS_REITERACAO');
                                $obj1->value = $calculo_final_dias_reiteracao;            

                                $obj18 = new \StdClass(); 
                                $obj18->id = env('FIELD_PRAZO_ID');
                                $obj18->value = ($demanda_detalhes->return->prazo?$this->formataDataBR($demanda_detalhes->return->prazo):'');            




                                $body->ticket->custom_fields = [
                                    0 => $obj0,
                                    1 => $obj1,
                                    2 => $obj18                                      
                                ];  

                                
                                $res4 = $HttpClient->put(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'.$checka_DemandaEXISTE->zendesk_ticket_id   , [
                                    'headers' => [
                                        'Accept' => 'application/json',
                                        'Content-Type' => 'application/json',
                                    ],
                                    'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                    'body' => json_encode($body) 
                                ]);

                                //Obtem objeto de retorno 
                                $objetoRetornoZendesk = $res4->getBody(); 
                                $objetoRetornoZendeskTratado = json_decode((string) $objetoRetornoZendesk);    
                             
                            }catch(\Exception $e){
                                                                
                                //LOG ERRO: Não foi possivel conectar-se ao zendesk para criar o comentário do ticket (Regulada Procedente)
                                LogsErros::create(['comando' => 'consultardemandasreinteracao', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel conectar-se ao zendesk para criar o comentário do ticket (comentário: Reinteração) :'.$e->getMessage() , 'codigo' => 71, 'demandas_id' => $checka_DemandaEXISTE->id ]);            
                                
                                //envia email
                                $n_demanda_p_email = (property_exists($demanda_detalhes->return, 'numeroDemanda') && is_int($demanda_detalhes->return->numeroDemanda))?$demanda_detalhes->return->numeroDemanda:'BACEN não informou este dado';
                                Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value, $n_demanda_p_email){
                                    $message->to($to_email)
                                    ->subject('Log - integrador')
                                    ->from('joao.peixoto.cd@gmail.com')  
                                    ->setBody('<b>comando:</b> consultardemandasreinteracao<br><br>'.'<b>Mensagem:</b> Demanda foi reinteração mas não foi possivel conectar-se ao zendesk para criar o comentário do ticket (comentário: Reinteração). Falha ao enviar ao Zendesk.'.'<br><br><b>Código de erro:</b> 71'.'<br><b>Instituição:</b>'.$instituicao_corrente_value.' <br><b>Numero Demanda:</b>'.$n_demanda_p_email, 'text/html');
                                });     


                                continue;
                                
                            }
                               
                            //ZENDESK &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                            //ZENDESK &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

                            
                            
                            //1)UPDATE em demandas, para alterar situacao_no_robo                                 
                            Demandas::where([['id', '=', $checka_DemandaEXISTE->id]])->update(['situacao_no_robo' => 'reinteracao_enviada_ao_zendesk' ]);       
                                      
                                
                            //$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%%$%$%$                                                                                                   

                                //5)ALTERAR  DATA_BUSCA_DEMANDAS_BACEN para CURDATE() 

                                //KERNEL (definir periodicidade)  
                            
                            //$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%%$%$%$    



                        }
                    }                    


                }
            }            

        }
        

        
                
        unset($soapClient);
        } //foreach de Instituições //&%^*&%&^%&^%&^$%^$^%$^%(*&(*&(*^!@)@()))



       //Display Script End time
        $time_end = microtime(true);        
        $execution_time = ($time_end - $time_start);
        $execution_time = ceil($execution_time);

        if($totalDemandas->return && is_int($totalDemandas->return)){
            LogsComandos::create(['comando' => 'consultardemandasreinteracao', 'data' => date('Y-m-d H:i:s'), 'duracao' => $execution_time, 'totalDemandasRetornadasRDRWebservice' => $totalDemandas->return ]);
        }              

        //execution time of the script 
        print_r ('Total Execution Time: '.$execution_time.' Segundos');         
        
    }


    
    public function formataData($data){
        $dataExplodida = explode('T', $data);
        $timeExplodido = explode('.', $dataExplodida[1]);

        return $dataExplodida[0].' '.$timeExplodido[0];
    }

    public function formataDataBR($data){
        $dataExplodida = explode('T', $data);
        $timeExplodido = explode('.', $dataExplodida[1]);

        $dataTratada = implode('/', array_reverse(explode('-', $dataExplodida[0])));

        //return $dataTratada.' '.$timeExplodido[0];          
        //return $dataTratada;
        return $dataExplodida[0];
    }


    public function getDataCorrente(){
        return date('Y-m-d'); 
    }



    public function getYesterday(){
        
        return date('Y-m-d', strtotime("-1 days"));           
       
    }




}
