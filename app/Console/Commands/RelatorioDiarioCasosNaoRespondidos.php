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
use App\AnexosBacen;        

use Mail;



class RelatorioDiarioCasosNaoRespondidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relatoriodiariocasosnaorespondidos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Relatorio Diario de Casos nao Respondidos';

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


        ////////////////////////////////////////////////////////////////////////////////////////////////
        $DEMANDAS_NAO_RESPONDIDAS = [];
        ////////////////////////////////////////////////////////////////////////////////////////////////



        //--- seta timezone    
        date_default_timezone_set('America/Sao_Paulo');

        //Aloca memória 
        ini_set("memory_limit", "2048M"); 

        
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
                LogsErros::create(['comando' => 'relatoriodiariocasosnaorespondidos', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas.', 'codigo' => 1 ]);

                //envia email
                Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value){
                    $message->to($to_email)
                        ->cc('lideres.rdr@mercadolivre.com')
                        ->subject('Relatório diário integrador - PROBLEMA')
                        ->from('joao.peixoto.cd@gmail.com')  
                        ->setBody('<b>comando:</b> relatoriodiariocasosnaorespondidos<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN indisponível ou credenciais desativadas/alteradas. Não foi possível gerar relatório diário.'.'<br><br><b>Código de erro:</b> 1'.'<br><b>Instituição:</b>'.$instituicao_corrente_value, 'text/html');
                });     

                //Finaliza comando
                return;
        }


            
        //requisita Novas demandas 
        //Algoritmo
        
        //Total de novas demandas na data corrente
        $parametros = [
            'dataInicio' => '2005-05-05T00:00:00.000', //Alterar para data corrente do servidor ***********!!!!!!!!      
            'dataFim' => $this->getDataCorrente().'T10:59:00.000', //Alterar para data corrente do servidor  *************!!!!!!!!
            'tipoConsulta' => "A", //Data de disponibilização    !!!!! A !!!!!!                        
            'situacao' => 16  //Pendente IF/AC  !!!!! 16 !!!!!!!           
        ];

        try{
            $totalDemandas = $soapClient->getTotalDemandas($parametros);    
        } catch(\Exception $e){    
            
            //echo ('Soap Exception: ' . $e->getMessage());

            //LOG ERRO: REGISTRAR INDISPONIBILIDADE DO WEBSERVICE NO NOSSO BD, ou credenciais desativadas/alteradas
            LogsErros::create(['comando' => 'relatoriodiariocasosnaorespondidos', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas ou método getTotalDemandas indisponível', 'codigo' => 2 ]);

            //envia email
            Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value ){
                $message->to($to_email)
                    ->cc('lideres.rdr@mercadolivre.com')
                    ->subject('Relatório diário integrador - PROBLEMA')
                    ->from('joao.peixoto.cd@gmail.com')  
                    ->setBody('<b>comando:</b> relatoriodiariocasosnaorespondidos<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN indisponível ou credenciais desativadas/alteradas ou método getTotalDemandas indisponível. Não foi possível gerar o relatório.'.'<br><br><b>Código de erro:</b> 2'.'<br><b>Instituição:</b>'.$instituicao_corrente_value , 'text/html');
            });     

            //Finaliza comando
            return;
        }
        
        //Se não retornou um inteiro em $totalDemandas->return 
        if(!is_object($totalDemandas) ||  (is_object($totalDemandas) && !property_exists($totalDemandas, "return")) || (is_object($totalDemandas) && property_exists($totalDemandas, "return") && !is_int($totalDemandas->return))){
            
            //LOG ERRO: registra no BD retorno inválido referente ao número de demandas
            LogsErros::create(['comando' => 'relatoriodiariocasosnaorespondidos', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN retornou um valor inválido para o número de novas demandas que foi consultado. Não foi possível gerar o relatório.', 'codigo' => 3 ]);  


            //envia email
            Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value ){
                $message->to($to_email)
                    ->cc('lideres.rdr@mercadolivre.com')
                    ->subject('Relatório diário integrador - PROBLEMA')
                    ->from('joao.peixoto.cd@gmail.com')  
                    ->setBody('<b>comando:</b> relatoriodiariocasosnaorespondidos<br><br>'.'<b>Mensagem:</b> WebService RDR BACEN retornou um valor inválido para o número de novas demandas que foi consultado. Não foi possível gerar o relatório.'.'<br><br><b>Código de erro:</b> 3'.'<br><b>Instituição:</b>'.$instituicao_corrente_value, 'text/html');
            });     


            //Finaliza comando
            return;
        }

        //calcula paginação
        $npaginas = 0;

        if($totalDemandas->return == 0){ //Nenhuma nova demanda

            //LOG ERRO nenhuma nova demanda foi encontrada Nesta Instituição
            LogsErros::create(['comando' => 'relatoriodiariocasosnaorespondidos', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Ocorrência: nenhuma nova demanda foi encontrada na Instituição corrente'.' Instituição:'.$value_Inst['user'] , 'codigo' => 4 ]);   

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
                'dataInicio' => '2005-05-05T00:00:00.000', //Alterar para data corrente do servidor ***********!!!!!!!
                'dataFim' => $this->getDataCorrente().'T10:59:00.000',  //Alterar para data corrente do servidor  *************!!!!!!!      
                'tipoConsulta' => "A", //Data de disponibilização  !!!! A !!!!
                'situacao' => 16,  //Pendente IF/AC  !!!!! 16 !!!!!!        
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
                            
                            if(!is_object($demanda_detalhes) || (is_object($demanda_detalhes) && !property_exists($demanda_detalhes, "return")) || !is_int($demanda_detalhes->return->idInterno)){
                                    
                                    //LOG ERRO: Registrar falha de leitura de demanda
                                    LogsErros::create(['comando' => 'relatoriodiariocasosnaorespondidos', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Falha na leitura de demanda do WebServive BACEN: Demanda com valores obrigatórios ausentes.', 'codigo' => 5 ]);  
                                    
                                    //envia email
                                    Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value ){
                                        $message->to($to_email)
                                        ->cc('lideres.rdr@mercadolivre.com')
                                        ->subject('Relatório diário integrador - PROBLEMA')
                                        ->from('joao.peixoto.cd@gmail.com')  
                                        ->setBody('<b>comando:</b> relatoriodiariocasosnaorespondidos<br><br>'.'<b>Mensagem:</b> Falha na leitura de demanda do WebServive BACEN: Demanda com estrutura inválida: Sem idEncaminhamento ou com corpo de dados diferente do formato esperado. Esta demanda não entrará no relatório diário.'.'<br><br><b>Código de erro:</b> 5'.'<br><b>Instituição:</b>'.$instituicao_corrente_value  , 'text/html');
                                    });      

                                    continue;  
                                    
                            }        

                                                    
                            //verifica se demanda têm numeroDemanda
                            $checkagem_numeroDemanda = (property_exists($demanda_detalhes->return, 'numeroDemanda') && is_int($demanda_detalhes->return->numeroDemanda)?$demanda_detalhes->return->numeroDemanda:false);
                            if($checkagem_numeroDemanda == false){


                                //LOG ERRO: Registrar falha de leitura de demanda
                                LogsErros::create(['comando' => 'relatoriodiariocasosnaorespondidos', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Falha na leitura de demanda do WebServive BACEN: Demanda com valores obrigatórios ausentes. Demanda sem numeroDemanda', 'codigo' => 566 ]);  
                                    
                                //envia email
                                Mail::send(array(), array(), function ($message) use ($to_email, $instituicao_corrente_value ){
                                    $message->to($to_email)
                                    ->cc('lideres.rdr@mercadolivre.com')
                                    ->subject('Relatório diário integrador - PROBLEMA')
                                    ->from('joao.peixoto.cd@gmail.com')  
                                    ->setBody('<b>comando:</b> relatoriodiariocasosnaorespondidos<br><br>'.'<b>Mensagem:</b> Falha na leitura de demanda do WebServive BACEN: Demanda com estrutura inválida: Demanda sem Número de Demanda. Esta demanda não entrará no relatório diário.'.'<br><br><b>Código de erro:</b> 5'.'<br><b>Instituição:</b>'.$instituicao_corrente_value  , 'text/html');
                                });      

                                continue;  

                            }
                            


                            // biblioteca https guzzle
                            $parametro_SSL = '';
                            if(env('AMBIENTE_LOCAL') == 's'){
                                $parametro_SSL = ['verify' => false];
                            }else{
                                $parametro_SSL = []; 
                            }
                            $HttpClient = new \GuzzleHttp\Client($parametro_SSL);






                            //VERIFICA SE DEMANDA EXISTE NO ZENDESK (AFIRMACAO: TODAS DEMANDAS DO ZENDESK PASSAM POR AKI)                            
                                //verifica se demanda existe na nossa base    
                                $checka_DemandaBASEMYSQL = Demandas::where([['idInternoBacenWebService', '=', $demanda_detalhes->return->idInterno]])->first();
                                if($checka_DemandaBASEMYSQL){
                                    
                                    //verifica se tem ticket
                                    if($checka_DemandaBASEMYSQL->zendesk_ticket_id){

                                        
                                        //consulta status do ticket $checka_DemandaBASEMYSQL->zendesk_ticket_id no zendesk
                                         //Passo 2 Cria comentário no Zendesk com anexos do passo 1)
                                        //Passo 2

                                        $statusNoZEND = '';

                                        try{ 
                                            
                                            $res4 = $HttpClient->get(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets/'.$checka_DemandaBASEMYSQL->zendesk_ticket_id  , [
                                                'headers' => [
                                                    /* 'Accept' => 'application/json', */
                                                    'Content-Type' => 'application/json' 
                                                ],
                                                'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                                /* 'body' => json_encode($body) */
                                            ]);

                                            //Obtem status desse ticket
                                            $createdComentZendesk = $res4->getBody(); 
                                            $createdComentZendeskTratado = json_decode((string) $createdComentZendesk);    
                                        
                                            $statusNoZEND = $createdComentZendeskTratado->ticket->status;


                                        }catch(\Exception $e){
                                                                            
                                            $statusNoZEND = 'Falha ao consultar status no Zendesk';
                                            
                                        }  

                                        //adicionar no array apenas fechados e resolvidos
                                        //nao enviar email se nao tiver nenhum caso

                                        //Kernel '20:00'
                                        //consultardemandasreinteracao: remover/comentar email de caso que nao entra e ficará travado 



                                        if($statusNoZEND == "closed" || $statusNoZEND == "solved"){

                                            //Adiciona Demanda no LOG
                                            $DEMANDAS_NAO_RESPONDIDAS[] = [
                                                'numeroDemanda' => $demanda_detalhes->return->numeroDemanda,
                                                'existeNoZendesk' => 's',
                                                'ticketNoZendesk' => $checka_DemandaBASEMYSQL->zendesk_ticket_id,
                                                'ticketNoZendeskStatus' => $statusNoZEND
                                            ];        

                                        }


                                    }else{

                                        //Adiciona Demanda no LOG
                                        $DEMANDAS_NAO_RESPONDIDAS[] = [
                                            'numeroDemanda' => $demanda_detalhes->return->numeroDemanda,
                                            'existeNoZendesk' => 'n',
                                            'ticketNoZendesk' => '',
                                            'ticketNoZendeskStatus' => ''
                                        ];        

                                    }

                                }else{ //Não está na nossa base, então com certeza não está no ZENDESK

                                    
                                    //Adiciona Demanda no LOG
                                    $DEMANDAS_NAO_RESPONDIDAS[] = [
                                        'numeroDemanda' => $demanda_detalhes->return->numeroDemanda,
                                        'existeNoZendesk' => 'n',
                                        'ticketNoZendesk' => '',
                                        'ticketNoZendeskStatus' => ''
                                    ];


                                } 


                            



                        }
                    }                    


                }
            }            

        }
        
        
        
        unset($soapClient);
        } //foreach de Instituições //&%^*&%&^%&^%&^$%^$^%$^%(*&(*&(*^!@)@()))






        ///////////////////////////////////////////////////*************************** */
        ///////////////////////////////////////////////////*************************** */
        ///////////////////////////////////////////////////*************************** */
        //ENVIA EMAIL DE LOG DIÁrio

        //apenas se tiver 1 ou mais casos que envia o email
        if($DEMANDAS_NAO_RESPONDIDAS){


            //MONTA STRING COM CASOS
            $strCASOS = '<b>Número Demanda</b> | <b>Existe no Zendesk</b> | <b>Ticket</b> | <b>Ticket Status</b> <br><br><br>';
            foreach($DEMANDAS_NAO_RESPONDIDAS as $key_____ => $value_____){

                $strCASOS .= $value_____['numeroDemanda'].' | '.$value_____['existeNoZendesk'].' | '.$value_____['ticketNoZendesk'].' | '.$value_____['ticketNoZendeskStatus'].'<br>'; 

            }

            //envia email

            //envia email
            Mail::send(array(), array(), function ($message) use ($to_email, $strCASOS){
                $message->to($to_email)
                ->cc('lideres.rdr@mercadolivre.com')
                ->subject('Relatório diário integrador - DETALHES')
                ->from('joao.peixoto.cd@gmail.com')  
                ->setBody($strCASOS  , 'text/html');
            });      


        }           

        //////////////////////////////////////////////////////////////////////////////////// 




        //Display Script End time
        $time_end = microtime(true);        
        $execution_time = ($time_end - $time_start);
        $execution_time = ceil($execution_time);
        

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
