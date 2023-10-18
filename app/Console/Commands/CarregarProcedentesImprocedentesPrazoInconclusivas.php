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



class CarregarProcedentesImprocedentesPrazoInconclusivas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carregarprocedentesimprocedentesprazoinconclusivas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando responsável por capturar demandas (procedentes, improcedentes, nao conclusivas e nao reguladas que nao existem na base mysql/zendesk e incluilas em ambos';

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

               //Aloca memória 
               ini_set("memory_limit", "2048M"); 
       
               $time_start = microtime(true);
               

               //#@#@#@#@#@#@#@#@#@#@#@#@#@##@#@##@#@#@
               $tiposDemandaTTT = [                   
                   20 => 'E', // improcedente
                   39 => 'E', //procedente
                   92 => 'E', //Inconclusiva 
                   130 => 'E', //não regulada
                   132 => 'S', //prazo aceito
                   135 => 'S' //prazo nao aceito   
               ];

               $tiposDemandaTTTZENDESK = [                   
                20 => 'encerrada_reclamacao_regulada_improcedente', // improcedente
                39 => 'encerrada_reclamacao_regulada_procedente', //procedente
                92 => 'encerrada_nao_conclusiva', //Inconclusiva 
                130 => 'encerrada_reclamacao_nao_regulada', //não regulada
                132 => 'pendente_if_ac_solicitacao_de_prazo_aceita', //prazo aceito
                135 => 'pendente_if_ac_solicitacao_de_prazo_nao_aceita' //prazo nao aceito   
            ];
            
            

               $ARR_nao_enviado_zendesk = [
                    20 => 'regulada_improcedente_nao_enviada_ao_zendesk', // improcedente
                    39 => 'regulada_procedente_nao_enviada_ao_zendesk', //procedente
                    92 => 'regulada_naoconclusiva_nao_enviada_ao_zendesk', //Inconclusiva 
                    130 => 'naoregulada_nao_enviada_ao_zendesk', //não regulada
                    132 => 'solicitacao_prazo_aceita_nao_enviada_ao_zendesk', //prazo aceito
                    135 => 'solicitacao_prazo_nao_aceita_nao_enviada_ao_zendesk' //prazo nao aceito  
               ];

               $ARR_enviado_zendesk = [
                    20 => 'regulada_improcedente_enviada_ao_zendesk', // improcedente
                    39 => 'regulada_procedente_enviada_ao_zendesk', //procedente
                    92 => 'regulada_naoconclusiva_enviada_ao_zendesk', //Inconclusiva 
                    130 => 'naoregulada_enviada_ao_zendesk', //não regulada
                    132 => 'solicitacao_prazo_aceita_enviada_ao_zendesk', //prazo aceito
                    135 => 'solicitacao_prazo_nao_aceita_enviada_ao_zendesk' //prazo nao aceito   
               ];

               
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
                       LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas', 'codigo' => 1 ]);
       
                       //Finaliza comando
                       return;
               }
       
       
                   
               //requisita Novas demandas 
               //Algoritmo
               
               foreach($tiposDemandaTTT as $keyTTT => $valueTTT){

               


               //Total de novas demandas na data corrente
               $parametros = [
                   'dataInicio' => $this->getYesterday().'T00:00:00.000', //Alterar para data corrente do servidor ***********!!!!!!!!      
                   'dataFim' => $this->getDataCorrente().'T23:59:00.000', //Alterar para data corrente do servidor  *************!!!!!!!!
                   'tipoConsulta' => $valueTTT,     
                   'situacao' => $keyTTT  
               ];
       
               try{
                   $totalDemandas = $soapClient->getTotalDemandas($parametros);    
               } catch(\Exception $e){    
                   
                   //echo ('Soap Exception: ' . $e->getMessage());
       
                   //LOG ERRO: REGISTRAR INDISPONIBILIDADE DO WEBSERVICE NO NOSSO BD, ou credenciais desativadas/alteradas
                   LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN indisponível ou credenciais desativadas/alteradas ou método getTotalDemandas indisponível', 'codigo' => 2 ]);
       
                   //Finaliza comando
                   return;
               }
               
               //Se não retornou um inteiro em $totalDemandas->return 
               if(!is_object($totalDemandas) ||  (is_object($totalDemandas) && !property_exists($totalDemandas, "return")) || (is_object($totalDemandas) && property_exists($totalDemandas, "return") && !is_int($totalDemandas->return))){
                   
                   //LOG ERRO: registra no BD retorno inválido referente ao número de demandas
                   LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'WebService RDR BACEN retornou um valor inválido para o número de demandas que foi consultado', 'codigo' => 3 ]);  
       
                   //Finaliza comando
                   return;
               }
       
               //calcula paginação
               $npaginas = 0;
       
               if($totalDemandas->return == 0){ //Nenhuma nova demanda
       
                   //LOG ERRO nenhuma nova demanda foi encontrada Nesta Instituição
                   LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Ocorrência: nenhuma demanda foi encontrada na Instituição corrente', 'codigo' => 4 ]);   
       
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
                       'dataFim' => $this->getDataCorrente().'T23:59:00.000',  //Alterar para data corrente do servidor  *************!!!!!!!      
                       'tipoConsulta' => $valueTTT, 
                       'situacao' => $keyTTT,  
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
                                   
                                   if(!is_object($demanda_detalhes) || (is_object($demanda_detalhes) && !property_exists($demanda_detalhes, "return")) || (is_object($demanda_detalhes) && property_exists($demanda_detalhes, "return") && !property_exists($demanda_detalhes->return, "instituicao")) || !is_object($demanda_detalhes->return->instituicao) || !is_int($demanda_detalhes->return->instituicao->idBacen) ||
                                      !property_exists($demanda_detalhes->return, "situacao") || !is_object($demanda_detalhes->return->situacao) || !is_int($demanda_detalhes->return->situacao->id) || 
                                      !is_int($demanda_detalhes->return->idInterno) ||
                                      !is_int($demanda_detalhes->return->idEncaminhamento)){
                                          
                                          //LOG ERRO: Registrar falha de leitura de demanda
                                          LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Falha na leitura de demanda do WebServive BACEN: Demanda com valores obrigatórios ausentes', 'codigo' => 5 ]);  
                                          
                                          continue;  
                                          
                                   }        
       
       
                                   //checka se a instituição financeira da demanda existe ou não no BD
                                   $instituicao_financeira_id = NULL;
                                   $instituicao_financeira_nome = '';
                                   $checka_InstituicaoFinanceira = InstituicaoFinanceira::where([['idBacenWebService', '=', $demanda_detalhes->return->instituicao->idBacen]])->first();    
                                   if(empty($checka_InstituicaoFinanceira)){  
       
                                       //insere instituição financeira
                                       $InstituicaoInserida = InstituicaoFinanceira::create(['idBacenWebService' => $demanda_detalhes->return->instituicao->idBacen, 'cnpj' => $demanda_detalhes->return->instituicao->cnpj, 'nome' => trim($demanda_detalhes->return->instituicao->nome)]);
                                       $instituicao_financeira_id = $InstituicaoInserida->id;    
                                       $instituicao_financeira_nome = $InstituicaoInserida->nome;
                                   }else{
                                       $instituicao_financeira_id = $checka_InstituicaoFinanceira->id;
                                       $instituicao_financeira_nome = $checka_InstituicaoFinanceira->nome;
                                   }  
                                   
       
       
                                   //checka se a situação_bacen da demanda existe ou não no BD
                                   $situacao_bacen_id = NULL;
                                   $situacao_bacen_nome = '';
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
                                   
                                   
       
                                   //checka se o canal de atendimento da demanda existe ou não no BD
                                   $canal_atendimento_id = NULL;
                                   if(property_exists($demanda_detalhes->return, 'canalAtendimento') && is_object($demanda_detalhes->return->canalAtendimento)){
                                       $checka_CanalAtendimento = CanalAtendimento::where([['idBacenWebService', '=', $demanda_detalhes->return->canalAtendimento->id]])->first();    
                                       if(empty($checka_CanalAtendimento)){  
       
                                           //insere Canal de atendimento
                                           $CanalAtendimentoInserido = CanalAtendimento::create(['idBacenWebService' => $demanda_detalhes->return->canalAtendimento->id, 'descricao' => $demanda_detalhes->return->canalAtendimento->descricao ]);
                                           $canal_atendimento_id = $CanalAtendimentoInserido->id;    
                                       }else{
                                           $canal_atendimento_id = $checka_CanalAtendimento->id; 
                                       }  
                                   }    
       
                                   
                                   //checka se o motivo da demanda existe ou não no BD
                                   $motivo_id = NULL;
                                   $motivo_nome = '';  
                                   if(property_exists($demanda_detalhes->return, 'motivo') && is_object($demanda_detalhes->return->motivo)){
                                       $checka_Motivo = Motivo::where([['idBacenWebService', '=', $demanda_detalhes->return->motivo->id]])->first();    
                                       if(empty($checka_Motivo)){  
       
                                           //insere Motivo
                                           $MotivoInserido = Motivo::create(['idBacenWebService' => $demanda_detalhes->return->motivo->id, 'descricao' => $demanda_detalhes->return->motivo->descricao ]);
                                           $motivo_id = $MotivoInserido->id;    
                                           $motivo_nome = $MotivoInserido->descricao;    
                                       }else{
                                           $motivo_id = $checka_Motivo->id; 
                                           $motivo_nome = $checka_Motivo->descricao;    
                                       }  
                                   }    
                                 
                                   
                                   //checka se o tipo_registro da demanda existe ou não no BD
                                   $tipo_registro_id = NULL;
                                   $tipo_registro_nome = '';
                                   if(property_exists($demanda_detalhes->return, 'tipoRegistro') && is_object($demanda_detalhes->return->tipoRegistro)){
                                       $checka_TipoRegistro = TipoRegistro::where([['idBacenWebService', '=', $demanda_detalhes->return->tipoRegistro->id]])->first();    
                                       if(empty($checka_TipoRegistro)){  
       
                                           //insere Tipo Registro
                                           $TipoRegistroInserido = TipoRegistro::create(['idBacenWebService' => $demanda_detalhes->return->tipoRegistro->id, 'descricao' => $demanda_detalhes->return->tipoRegistro->descricao ]);
                                           $tipo_registro_id = $TipoRegistroInserido->id;    
                                           $tipo_registro_nome = $TipoRegistroInserido->descricao;
                                       }else{
                                           $tipo_registro_id = $checka_TipoRegistro->id; 
                                           $tipo_registro_nome = $checka_TipoRegistro->descricao;
                                       }  
                                   }   
                                   
                                   
                                   //checka se é uma demanda que está chegando do NADA (sem criação prévia)
                                   //checka se é uma demanda que está chegando do NADA (sem criação prévia)
                                   //checka se é uma demanda que está chegando do NADA (sem criação prévia)
                                   //checka se é uma demanda que está chegando do NADA (sem criação prévia)

                                   $checka_DemandaRepetida = Demandas::where([['idInternoBacenWebService', '=', $demanda_detalhes->return->idInterno]])->first();
                                   if($checka_DemandaRepetida){

                                        

                                       continue;
                                   } 
       
       
                                   //cria um array associativo para INSERT da demanda
                                   $demanda_inserir = [
       
                                       'instituicao_financeira_id' => $instituicao_financeira_id,
                                       'situacao_bacen_id' => $situacao_bacen_id,
                                       'canal_atendimento_id' => $canal_atendimento_id,
                                       'motivo_id' => $motivo_id,
                                       'tipo_registro_id' => $tipo_registro_id,
                                       'numeroDemanda' => (property_exists($demanda_detalhes->return, 'numeroDemanda') && is_int($demanda_detalhes->return->numeroDemanda)?$demanda_detalhes->return->numeroDemanda:NULL), 
                                       'idInternoBacenWebService' => $demanda_detalhes->return->idInterno,
                                       'indicadorLido' => 0,
                                       'dataCadastro' => ($demanda_detalhes->return->dataCadastro?$this->formataData($demanda_detalhes->return->dataCadastro):NULL),
                                       'dataDisponibilizacao' => ($demanda_detalhes->return->dataDisponibilizacao?$this->formataData($demanda_detalhes->return->dataDisponibilizacao):NULL),
                                       'dataNotificacao' => ($demanda_detalhes->return->dataNotificacao?$this->formataData($demanda_detalhes->return->dataNotificacao):NULL),
                                       'dataProtocolo' => ($demanda_detalhes->return->dataProtocolo?$this->formataData($demanda_detalhes->return->dataProtocolo):NULL),
                                       'protocoloIF' => ($demanda_detalhes->return->protocoloIF?trim($demanda_detalhes->return->protocoloIF):NULL),    
                                       'descricao' => ($demanda_detalhes->return->descricao?trim($demanda_detalhes->return->descricao):NULL),
                                       'prazo' => ($demanda_detalhes->return->prazo?$this->formataData($demanda_detalhes->return->prazo):NULL),
                                       'situacao_no_robo' => $ARR_nao_enviado_zendesk[$keyTTT] //primeira situação de uma demanda no fluxo
                                       

                                   ];
                                   $demanda_inserida = Demandas::create($demanda_inserir);
                                   $demanda_inserida_id = $demanda_inserida->id;
       
                                   //insere cidadao
                                   if(property_exists($demanda_detalhes->return, 'cidadao') && is_object($demanda_detalhes->return->cidadao) && !empty($demanda_detalhes->return->cidadao->nome) && !empty($demanda_detalhes->return->cidadao->emails)){
                                         
                                       $cidadao_inserir = [
                                           'demandas_id' => $demanda_inserida_id,
                                           'nome' => base64_encode($demanda_detalhes->return->cidadao->nome),
                                           'documento' =>  ($demanda_detalhes->return->cidadao->documento?base64_encode(trim($demanda_detalhes->return->cidadao->documento)):NULL),
                                           'tipoDocumento' => ($demanda_detalhes->return->cidadao->tipoDocumento?base64_encode(trim($demanda_detalhes->return->cidadao->tipoDocumento)):NULL), 
                                           'emails' => ($demanda_detalhes->return->cidadao->emails?base64_encode(trim($demanda_detalhes->return->cidadao->emails)):NULL), 
                                           'telefones' => ($demanda_detalhes->return->cidadao->telefones?base64_encode(trim($demanda_detalhes->return->cidadao->telefones)):NULL), 
                                           'cep' => ($demanda_detalhes->return->cidadao->cep?base64_encode(trim($demanda_detalhes->return->cidadao->cep)):NULL), 
                                           'uf'  => ($demanda_detalhes->return->cidadao->uf?base64_encode(trim($demanda_detalhes->return->cidadao->uf)):NULL),                                     
                                           'municipio_descricao' => ($demanda_detalhes->return->cidadao->municipio->descricao?base64_encode(trim($demanda_detalhes->return->cidadao->municipio->descricao)):NULL), 
                                           'municipio_id' => ($demanda_detalhes->return->cidadao->municipio->id?$demanda_detalhes->return->cidadao->municipio->id:NULL), 
                                           'endereco' => ($demanda_detalhes->return->cidadao->endereco?base64_encode(trim($demanda_detalhes->return->cidadao->endereco)):NULL), 
                                           'bairro' => ($demanda_detalhes->return->cidadao->bairro?base64_encode(trim($demanda_detalhes->return->cidadao->bairro)):NULL), 
                                           'numero' => ($demanda_detalhes->return->cidadao->numero?base64_encode(trim($demanda_detalhes->return->cidadao->numero)):NULL), 
                                           'complemento' => ($demanda_detalhes->return->cidadao->complemento?base64_encode(trim($demanda_detalhes->return->cidadao->complemento)):NULL), 
                                           'cidadeUF' => ($demanda_detalhes->return->cidadao->cidadeUF?base64_encode(trim($demanda_detalhes->return->cidadao->cidadeUF)):NULL) 
                                       ];                               
                                       Cidadao::create($cidadao_inserir);        
       
                                   }else{
       
                                       //LOG ERRO: demanda sem cidadão
                                       LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Falha no insert do cidadão da demanda: demanda sem cidadão.', 'codigo' => 6, 'demandas_id' => $demanda_inserida_id ]);  
       
                                       continue;    
       
                                   }
       
       
                                   //Insere Encaminhamento
                                   $encaminhamento = Encaminhamentos::create(['demandas_id' => $demanda_inserida_id, 'idEncaminhamentoBacenWebService' => $demanda_detalhes->return->idEncaminhamento]);
                                   $encaminhamentos_id = $encaminhamento->id;
                                   
       
       
                                   /** ** ** ** ** ** ** ** ** ** */
                                   //Insere Anexos BACEN na BASE MYSQL e os envia ao zendesk
                                   //***** **** ** ** ** ** ** ** */ 
       
                                   // biblioteca https guzzle
                                   $parametro_SSL = '';
                                   if(env('AMBIENTE_LOCAL') == 's'){
                                       $parametro_SSL = ['verify' => false];
                                   }else{
                                       $parametro_SSL = []; 
                                   }
                                   $HttpClient = new \GuzzleHttp\Client($parametro_SSL);
       
       
                                   //Todo anexo enviado ao zendesk possui um token que é guardado neste array:
                                   $Lista_De_Tokens_Anexos_Zendesk = [];
       
       
                                   //se tem anexo
                                   if(property_exists($demanda_detalhes->return, 'anexo')){
       
                                       //se for apenas 1 anexo: anexo é 1 object com os dados do arquivo, então transformá-lo em array (similar linha 153)                                 
                                       //se retornou apenas um anexo, transforma em array 
                                       if(is_object($demanda_detalhes->return->anexo)){
                                           $demanda_detalhes->return->anexo = [ 0 => $demanda_detalhes->return->anexo ];
                                       }
       
                                       //Processa anexo(s)
                                       if(is_array($demanda_detalhes->return->anexo)){
                                           foreach($demanda_detalhes->return->anexo as $key_anexo_it => $anexo_it){
       
                                              if(is_object($anexo_it) && $anexo_it->url && $anexo_it->nome){
                                                  //captura anexo com requisição autenticada
                                                   try{ 
                                                       //Requisição autenticada https para obter anexo (requisição retorna o binário do anexo)
                                                       $anexo__returned = $HttpClient->get( $anexo_it->url, [
                                                           'headers' => [                                                        
                                                           ],
                                                           'auth' =>  [ $USER_WEBSERVICE_RDR_BACEN , $PASSWORD_WEBSERVICE_RDR_BACEN ]                                                     
                                                       ]);
                                                       //extrai o binário
                                                       $anexo__returned_body = $anexo__returned->getBody(); 
                                                       
                                                       //insere no Robô-MySQL
                                                       // *******************
                                                       AnexosBacen::create(['encaminhamentos_id' => $encaminhamentos_id, 'idBacenWebService' => $anexo_it->id, 'nomeBacenWebService' => $anexo_it->nome, 'urlBacenWebService' => $anexo_it->url, 'arquivo' => base64_encode($anexo__returned_body) ]); 
                                                                                                       
                                                       
                                                       //Envia Anexo para o Zendesk e guarda token desse anexo 
                                                       // ***************                                                                                                                                                                    
                                                       $res1 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/uploads'   , [
                                                           'headers' => [
                                                               'Accept' => 'application/binary',
                                                               'Content-Type' => 'application/binary',
                                                           ],
                                                           'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                                           'body' => $anexo__returned_body,
                                                           'query' => ['filename' => $anexo_it->nome] //parâmetros 
                                                       ]);                  
                                                       //Obtem token desse arquivo recem criado no zendesk
                                                       $createdArquivoZendesk = $res1->getBody(); 
                                                       $createdArquivoZendeskTratado = json_decode((string) $createdArquivoZendesk);    
                                                       //GUARDA TOKEN EM Lista_De_Tokens_Anexos_Zendesk                                                                                             
                                                       $Lista_De_Tokens_Anexos_Zendesk[] = $createdArquivoZendeskTratado->upload->token; 
                                                       
                   
                                                   }catch(\Exception $e){                                                
                                                       
                                                       //LOG ERRO: não foi possivel capturar o anexo $anexo_it->url do BACEN
                                                       LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel capturar do RDR-BACEN o anexo: '.$anexo_it->url.' .Demanda não enviada ao zendesk. erro:'.$e->getMessage(), 'codigo' => 88, 'demandas_id' => $demanda_inserida_id ]);                                                           
                   
                                                       //Não envia ao zendesk e pula para próxima demanda. 
                                                       continue;
                                                   }  
       
                                                  
                                              } 
       
                                           }
                                       }
       
                                   }
                                   /** ** ** ** ** ** ** ** ** ** */                            
                                   //***** **** ** ** ** ** ** ** */
       
       
       
                                   
       
                                   /*****************************************/ 
                                   /******************ZENDESK ***************/
                                   /*****************************************/
       
                                   
                                   //ZENDESK &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                                   //ZENDESK &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&                            
       
                                   //ENVIA DADOS PARA ZENDESK via biblioteca https guzzle
                                   //Biblioteca https guzzle instanciada um pouco acima, para capturar anexo do BACEN
       
                                                               
                                   //ENVIA DADOS PARA ZENDESK 
       
                                       //[[ETAPA 1]]
                                       //[[ETAPA 1]] geração do requester_id no zendesk ou caso exista, obtenção do requester_id (cidadão reclamante == requester_id)
                                       //parâmetro: email único no zendesk
                                                                                        
       
                                       try{ 
                                           //verifica se existe um usuário zendesk com email == $demanda_detalhes->return->cidadao->emails ?
                                           $res = $HttpClient->get(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/users/search'   , [
                                               'headers' => [
                                                   'Accept' => 'application/json',
                                                   'Content-Type' => 'application/json',
                                               ],
                                               'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                               'query' => ['query' => $demanda_detalhes->return->cidadao->emails] //parâmetros de busca
                                           ]);
                                           
                                           $objetoRetorno = $res->getBody(); 
                                           $objetoRetornoTratado = json_decode((string) $objetoRetorno); 
       
                                       }catch(\Exception $e){
                                           echo $e->getMessage(); 
                                           
                                           //LOG ERRO: não foi possivel conectar-se ao zendesk
                                           LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel conectar-se ao Zendesk', 'codigo' => 7, 'demandas_id' => $demanda_inserida_id ]);    
       
                                           //TODO:
                                           //status_demanda_robo == 'nova_nao_enviada_ao_zendesk'
       
                                           //continua obtendo demandas e armazenando na base
                                           continue;
                                       }  
       
                                       
       
                                       $requester_id = NULL;
       
                                       if(empty($objetoRetornoTratado->users)){ //se não existe este email no zendesk, cria este usuario/requester
                                           
                                           try{ 
                                               //solicita criação de usuário/requester 
       
                                               $body = new \StdClass();
                                               $body->user = new \StdClass();
                                               $body->user->email = $demanda_detalhes->return->cidadao->emails;
                                               $body->user->name = $demanda_detalhes->return->cidadao->nome;
                                                
       
                                               $res2 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/users'   , [
                                                   'headers' => [
                                                       'Accept' => 'application/json',
                                                       'Content-Type' => 'application/json',
                                                   ],
                                                   'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                                   'body' => json_encode($body)
                                               ]);
           
                                               //Obtem id desse usuario recem criado
                                               $createdRequesterZendesk = $res2->getBody(); 
                                               $createdRequesterZendeskTratado = json_decode((string) $createdRequesterZendesk);    
                                                                                                                              
                                               $requester_id = $createdRequesterZendeskTratado->user->id;                                        
       
       
                                           }catch(\Exception $e){
                                               echo $e->getMessage(); 
                                               
                                               //LOG ERRO: não foi possivel conectar-se ao zendesk para criar novo usuário
                                               LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel conectar-se ao zendesk para criar novo usuário-requester', 'codigo' => 8, 'demandas_id' => $demanda_inserida_id ]);    
       
                                               //TODO:
                                               //status_demanda_robo == 'nova_nao_enviada_ao_zendesk'
           
                                               //continua obtendo demandas e armazenando na base
                                               continue;
                                           }  
       
                                       }else{ //este email existe no zendesk, obtém este usuário 
       
       
                                           //se existe mais de uma ocorrência deste email no zendesk
                                               //não faz nada e vai para proxima iteração, pois trata-se de um email problemático: que ocorre mais de uma vez no zendesk
                                           if(is_array($objetoRetornoTratado->users) && count($objetoRetornoTratado->users) > 1){
       
                                               //LOG ERRO: não foi possível criar demanda no zendesk, email Duplicado no zendesk
                                               LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possível criar demanda no zendesk, email duplicado no Zendesk', 'codigo' => 9, 'demandas_id' => $demanda_inserida_id ]);              
       
                                               continue;
                                           }    
       
                                           //Encontrou usuário no Zendesk, obtém este usuário
                                           if(is_array($objetoRetornoTratado->users) && count($objetoRetornoTratado->users) == 1){ 
       
                                               $requester_id = $objetoRetornoTratado->users[0]->id;
       
                                           }
       
                                           if(!is_array($objetoRetornoTratado->users)){
       
                                               //LOG ERRO: retorno inválido do ZENDESK
                                               LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Retorno inválido do ZENDESK', 'codigo' => 10, 'demandas_id' => $demanda_inserida_id ]);    
       
                                               continue;
       
                                           }
       
                                       }    
                                   
       
                                       //[[ETAPA 2]]
                                       //[[ETAPA 2]] Faz upload dos possíveis anexos da demanda no Zendesk, obtendo o TOKEN de cada arquivo. (1 Por vez)
       
                                          //Já Executada durante a consulta dos anexos BACEN num passo anterior
       
                                       
       
       
                                       //[[ETAPA 3]]
                                       //[[ETAPA 3]] Faz a criação do ticket no Zendesk para o solicitante/requester:  $requester_id
       
                                       $body = new \StdClass();
                                       $body->ticket = new \StdClass();                                
                                       $body->ticket->requester_id = $requester_id; 


                                                                           
                                       $subjectAUX = '';  
                                       if($keyTTT == 20){
                                            $subjectAUX = "####= Demanda Regulada Improcedente";
                                       }else if($keyTTT == 39){
                                            $subjectAUX = "####= Demanda Regulada Procedente"; 
                                       }else if($keyTTT == 92){
                                            $subjectAUX = "####= Demanda não conclusiva"; 
                                       }else if($keyTTT == 130){
                                            $subjectAUX = "####= Demanda não regulada"; 
                                       }else if($keyTTT == 132){
                                            $subjectAUX = "####= Demanda com Solicitação de Prazo Aceita"; 
                                       }else if($keyTTT == 135){
                                            $subjectAUX = "####= Demanda com Solicitação de Prazo Não Aceita";
                                       }

                                       $body->ticket->subject =  $subjectAUX;    
                                       

                                       $body->ticket->comment = new \StdClass();
                                       $body->ticket->comment->body = ($demanda_detalhes->return->descricao?$demanda_detalhes->return->descricao:'Demanda sem descrição');     
                                       $body->ticket->comment->public = false;     
                                       $body->ticket->status = "new";
                                       $body->ticket->group_id = env('GROUP_ID_ZENDESK'); 
                                       $body->ticket->priority = "normal";   
       
                                       
                                       $body->ticket->comment->uploads = $Lista_De_Tokens_Anexos_Zendesk; //Lista de tokens de anexos 
       
       
                                       $body->ticket->ticket_form_id = env('TICKET_FORM_ID');    
                                       
                                       $obj0 = new \StdClass();
                                       $obj0->id = env('FIELD_INSTITUICAO_FINANCEIRA_ID');
                                       $obj0->value = $instituicao_financeira_nome;
                                       
                                       $obj1 = new \StdClass();
                                       $obj1->id = env('FIELD_TIPO_DE_REGISTRO_ID');
                                       $obj1->value = $tipo_registro_nome;
                                       
                                       $obj2 = new \StdClass();
                                       $obj2->id = env('FIELD_MOTIVO_ID');
                                       $obj2->value = $motivo_nome;
                                       
                                       $obj3 = new \StdClass();
                                       $obj3->id = env('FIELD_SITUACAO_NO_BACEN_ID');
                                       $obj3->value = $tiposDemandaTTTZENDESK[$keyTTT];    
                                                                       
                                       $obj4 = new \StdClass();
                                       $obj4->id = env('FIELD_NOME_DO_SOLICITANTE_ID');
                                       $obj4->value = $demanda_detalhes->return->cidadao->nome;
                                       
                                       $obj5 = new \StdClass();
                                       $obj5->id = env('FIELD_DOCUMENTO_ID');
                                       $obj5->value = ($demanda_detalhes->return->cidadao->documento?trim($demanda_detalhes->return->cidadao->documento):''); 
                                       
                                       $obj6 = new \StdClass();
                                       $obj6->id = env('FIELD_TIPO_DE_DOCUMENTO_ID');
                                       $obj6->value = ($demanda_detalhes->return->cidadao->tipoDocumento?trim($demanda_detalhes->return->cidadao->tipoDocumento):''); 
                                       
                                       $obj7 = new \StdClass(); 
                                       $obj7->id = env('FIELD_E_MAILS_ID');
                                       $obj7->value = ($demanda_detalhes->return->cidadao->emails?trim($demanda_detalhes->return->cidadao->emails):''); 
                                      
                                       $obj8 = new \StdClass(); 
                                       $obj8->id = env('FIELD_TELEFONES_ID');
                                       $obj8->value = ($demanda_detalhes->return->cidadao->telefones?trim($demanda_detalhes->return->cidadao->telefones):'');   
       
                                       $obj9 = new \StdClass(); 
                                       $obj9->id = env('FIELD_CEP_ID');
                                       $obj9->value = ($demanda_detalhes->return->cidadao->cep?trim($demanda_detalhes->return->cidadao->cep):'');   
       
                                       $obj10 = new \StdClass(); 
                                       $obj10->id = env('FIELD_UF_ID');
                                       $obj10->value = ($demanda_detalhes->return->cidadao->uf?trim($demanda_detalhes->return->cidadao->uf):'');   
       
                                       $obj11 = new \StdClass(); 
                                       $obj11->id = env('FIELD_CIDADE_ID');
                                       $obj11->value = ($demanda_detalhes->return->cidadao->cidadeUF?trim($demanda_detalhes->return->cidadao->cidadeUF):'');   
       
                                       $obj12 = new \StdClass(); 
                                       $obj12->id = env('FIELD_NUMERO_DEMANDA_ID');
                                       $obj12->value = (property_exists($demanda_detalhes->return, 'numeroDemanda') && is_int($demanda_detalhes->return->numeroDemanda)?$demanda_detalhes->return->numeroDemanda:'');     
       
                                       $obj13 = new \StdClass(); 
                                       $obj13->id = env('FIELD_ID_INTERNO_BACEN_WEBSERVICE_ID');
                                       $obj13->value = $demanda_detalhes->return->idInterno;     
       
                                       $obj14 = new \StdClass(); 
                                       $obj14->id = env('FIELD_DATA_CADASTRO_ID');
                                       $obj14->value = ($demanda_detalhes->return->dataCadastro?$this->formataDataBR($demanda_detalhes->return->dataCadastro):'');     
       
                                       $obj15 = new \StdClass(); 
                                       $obj15->id = env('FIELD_DATA_DE_DISPONIBILIZACAO_ID');
                                       $obj15->value = ($demanda_detalhes->return->dataDisponibilizacao?$this->formataDataBR($demanda_detalhes->return->dataDisponibilizacao):'');    
                                       
                                       $obj16 = new \StdClass(); 
                                       $obj16->id = env('FIELD_DATA_PROTOCOLO_ID');
                                       $obj16->value = ($demanda_detalhes->return->dataProtocolo?$this->formataDataBR($demanda_detalhes->return->dataProtocolo):'');            
                                       
                                       $obj17 = new \StdClass(); 
                                       $obj17->id = env('FIELD_PROTOCOLO_IF_ID');
                                       $obj17->value = ($demanda_detalhes->return->protocoloIF?trim($demanda_detalhes->return->protocoloIF):'');            
       
                                       $obj18 = new \StdClass(); 
                                       $obj18->id = env('FIELD_PRAZO_ID');
                                       $obj18->value = ($demanda_detalhes->return->prazo?$this->formataDataBR($demanda_detalhes->return->prazo):'');            
       
                                       
                                       $body->ticket->custom_fields = [
                                           0 => $obj0,
                                           1 => $obj1,
                                           2 => $obj2,
                                           3 => $obj3,
                                           4 => $obj4,
                                           5 => $obj5,
                                           6 => $obj6,
                                           7 => $obj7,
                                           8 => $obj8,
                                           9 => $obj9,
                                           10 => $obj10,
                                           11 => $obj11,
                                           12 => $obj12,
                                           13 => $obj13,
                                           14 => $obj14,
                                           15 => $obj15,
                                           16 => $obj16,
                                           17 => $obj17,
                                           18 => $obj18
                                       ];  
                                       
                                       
                                       try{ 
       
                                           $res3 = $HttpClient->post(env('URL_ZENDESK').'/api/'.env('VERSAO_API_ZENDESK').'/tickets'   , [
                                               'headers' => [
                                                   'Accept' => 'application/json',
                                                   'Content-Type' => 'application/json',
                                               ],
                                               'auth' =>  [ env('USER_ZENDESK') , env('TOKEN_ZENDESK')],
                                               'body' => json_encode($body)
                                           ]);
       
                                           //Obtem id desse usuario recem criado
                                           $createdTicketZendesk = $res3->getBody(); 
                                           $createdTicketZendeskTratado = json_decode((string) $createdTicketZendesk);    
                                        
                                       }catch(\Exception $e){
                                           echo $e->getMessage();           
                                           
                                           //LOG ERRO: não foi possivel conectar-se ao zendesk para criar o ticket
                                           LogsErros::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'descricao' => 'Não foi possivel conectar-se ao Zendesk para criar o ticket', 'codigo' => 11, 'demandas_id' => $demanda_inserida_id ]);            
                                           
                                           //TODO:
                                           //status_demanda_robo == 'nova_nao_enviada_ao_zendesk'
       
                                           //continua obtendo demandas e armazenando na base
                                           continue;
                                       }  
                                       
       
                                       
       
       
                                       
                                       /*
                                       echo "\n";
                                       echo "\n";
                                       echo "---------------------------------------------------------------------";
                                       echo "\n";
                                       echo "---------------------------------------------------------------------";
                                       var_dump($createdTicketZendeskTratado);  
                                       echo "\n";
                                       */    
       
                                      
                                   //ZENDESK &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                                   //ZENDESK &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
       
                                   
                                   
                                   //1)UPDATE em demandas, para adicionar zendesk_ticket_id e zendesk_requester_id, situacao_no_robo                                 
                                   Demandas::where([['id', '=', $demanda_inserida_id]])->update(['zendesk_requester_id' => $requester_id, 'zendesk_ticket_id' => $createdTicketZendeskTratado->ticket->id, 'situacao_no_robo' => $ARR_enviado_zendesk[$keyTTT]]);       
                                         
                                   
                                       
                                   //$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%%$%$%$                                                                                                    
       
                                       //5)ALTERAR  DATA_BUSCA_DEMANDAS_BACEN para CURDATE() 
       
                                       //6)IMPLEMENTAR ANEXOS                                
       
                                       //FINALIZAR TODO
       
                                       //KERNEL (definir periodicidade)  
                                   
                                   //$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%%$%$%$    
       
       
       
                               }
                           }                    
       
       
                       }
                   }            
       
               }
               
       

               }   

               //checkSUM
                   //Total de demandas retornado por getTotalDemandas foi inserido no BD ?
                   //tratar este problema TODO
                   
       
               //enviar p/ Zendesk as obtidas neste comando 
               
               
               unset($soapClient);
               } //foreach de Instituições //&%^*&%&^%&^%&^$%^$^%$^%(*&(*&(*^!@)@()))
       
       
              //Display Script End time
               $time_end = microtime(true);        
               $execution_time = ($time_end - $time_start); 
               $execution_time = ceil($execution_time);
       
               if($totalDemandas->return && is_int($totalDemandas->return)){
                   LogsComandos::create(['comando' => 'carregarprocedentesimprocedentesprazoinconclusivas', 'data' => date('Y-m-d H:i:s'), 'duracao' => $execution_time, 'totalDemandasRetornadasRDRWebservice' => $totalDemandas->return ]);
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
