<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\ConsultarDemandasNaoConclusiva',
        'App\Console\Commands\ConsultarDemandasNaoRegulada',
        'App\Console\Commands\ConsultarDemandasReguladaImprocedente',
        'App\Console\Commands\consultardemandasreguladaprocedente',
        'App\Console\Commands\ConsultarDemandasReinteracao',
        'App\Console\Commands\ConsultarDemandasSolicitacaoPrazoAceita',
        'App\Console\Commands\ConsultarDemandasSolicitacaoPrazoNaoAceita',
        'App\Console\Commands\ConsultarNovasDemandasBacen',
        'App\Console\Commands\ReenvioAutomaticoIntegrador',
        'App\Console\Commands\RelatorioDiarioCasosNaoRespondidos'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {        
        //$schedule->command('consultarnovasdemandasbacen --force')->hourly()->onOneServer()->runInBackground();  //onOneServer evita de ser disparado o job por 2 servers,  runInBackground permite ao Schedulle disparar n comandos simultâneos
        
        $schedule->command('relatoriodiariocasosnaorespondidos')->dailyAt('20:00')->onOneServer()->runInBackground();         

        
        $schedule->command('consultarnovasdemandasbacen')->hourly()->onOneServer()->runInBackground();  
        

        //consulta Procedentes/Improcedentes/Inconclusivas/Nao reguladas/Prazo aceito/Prazo nao aceito que foram carregadas inicialmente pelo comando 'consultarnovasdemandasbacen'
        $schedule->command('consultardemandasnaoconclusiva')->hourly()->onOneServer()->runInBackground();  
        $schedule->command('consultardemandasnaoregulada')->hourly()->onOneServer()->runInBackground();  
        $schedule->command('consultardemandasreguladaimprocedente')->hourly()->onOneServer()->runInBackground();  
        $schedule->command('consultardemandasreguladaprocedente')->hourly()->onOneServer()->runInBackground();       
        $schedule->command('consultardemandasreinteracao')->hourly()->onOneServer()->runInBackground();
        $schedule->command('consultardemandassolicitacaoprazoaceita')->hourly()->onOneServer()->runInBackground();       
        $schedule->command('consultardemandassolicitacaoprazonaoaceita')->hourly()->onOneServer()->runInBackground();       



        //consulta Procedentes/Improcedentes/Inconclusivas/Nao reguladas/Prazo aceito/Prazo nao aceito que chegam sem ter sido carregadas previamente no zendesk  (chegam do nada, no inicio do projeto eram denominadas de 'travadas')      
        $schedule->command('carregarprocedentesimprocedentesprazoinconclusivas')->hourly()->onOneServer()->runInBackground();       
        
        

        //Reenvio Automatico a cada 15 minutos    
        $schedule->command('reenvioautomaticointegrador')->everyFifteenMinutes()->onOneServer()->runInBackground();

    }   

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
