<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSituacaoBacen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('situacao_bacen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idBacenWebService')->unsigned();  
            $table->string('descricao', 250)->nullable();    
            $table->boolean('alteravelIF');

        });


        //insere registro(s) padrão
        DB::table('situacao_bacen')->insert(
            [
                ['idBacenWebService' => 7245, 'descricao' => 'Acesso conc - Comunicada necessidade de pagamento', 'alteravelIF' => false], 
                ['idBacenWebService' => 7243, 'descricao' => 'Acesso conc - Concedido acesso a sist. corporativo', 'alteravelIF' => false], 
                ['idBacenWebService' => 7460, 'descricao' => 'Encerrada: cancelada após resposta da IF/AC', 'alteravelIF' => false], 
                ['idBacenWebService' => 7320, 'descricao' => 'Encerrada: Em liquidação/intervenção', 'alteravelIF' => false], 
                ['idBacenWebService' => 92,   'descricao' => 'Encerrada: não conclusiva', 'alteravelIF' => false], 
                ['idBacenWebService' => 130,  'descricao' => 'Encerrada: reclamação não regulada', 'alteravelIF' => false], 
                ['idBacenWebService' => 20,   'descricao' => 'Encerrada: reclamação regulada improcedente', 'alteravelIF' => false], 
                ['idBacenWebService' => 39,   'descricao' => 'Encerrada: reclamação regulada procedente', 'alteravelIF' => false],  
                ['idBacenWebService' => 16,   'descricao' => 'Pendente: IF/AC', 'alteravelIF' => true], 
                ['idBacenWebService' => 128,  'descricao' => 'Pendente: IF/AC Solicitação de prazo', 'alteravelIF' => true], 
                ['idBacenWebService' => 132,  'descricao' => 'Pendente: IF/AC Solicitação de prazo aceita', 'alteravelIF' => true], 
                ['idBacenWebService' => 136,  'descricao' => 'Pendente: IF/AC Solicitação de prazo cancelada', 'alteravelIF' => true], 
                ['idBacenWebService' => 135,  'descricao' => 'Pendente: IF/AC Solicitação de prazo não aceita', 'alteravelIF' => true] 
            ]
        );    


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('situacao_bacen');
    }
}
