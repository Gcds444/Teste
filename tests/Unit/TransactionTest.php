<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\TransactionController;
use App\Models\Client;

class TransactionTest extends TestCase
{
    //sequencia validation
    public function test_validation_dont_find_id_sql()
    {   
        $payer = [];
        $payee = [];
        $validated = [];

        $transaction = TransactionController::validation($payer, $payee, $validated);
        $this->assertEquals('pagante e(ou) recebedor não cadastrado(s)', $transaction['message']);
    }

    public function test_validation_payer_cant_be_type_1()
    {   
        $payer = [
            'id' => 1,
            'type' => 1,  
        ];
        $payee = [
            'id' => 2
        ];
        $validated = [];

        $transaction = TransactionController::validation($payer, $payee, $validated);
        $this->assertEquals('lojista não pode efetuar transações', $transaction['message']);
    }

    public function test_validation_insufficient_money()
    {   
        $payer = [
            'id' => 1,
            'type' => 0,
            'wallet' => '10.00'  
        ];
        $payee = [
            'id' => 2
        ];
        $validated = [
            'value' => '20.00'
        ];

        $transaction = TransactionController::validation($payer, $payee, $validated);
        $this->assertEquals('valor na carteira insuficiente', $transaction['message']);
    }

    public function test_validation_same_person()
    {   
        $payer = [
            'id' => 1,
            'type' => 0,
            'wallet' => '10.00'  
        ];
        $payee = [
            'id' => 1
        ];
        $validated = [
            'value' => '5.00'
        ];

        $transaction = TransactionController::validation($payer, $payee, $validated);
        $this->assertEquals('transação não pode ser feita entre a mesma pessoa', $transaction['message']);
    }

    //controle da carteira
    public function test_sum_wallet()
    {
        $person = [
            'wallet' => '20.50'
        ];
        $validated = [
            'value' => '15.25'
        ];
        $type = '+';

        $transaction = TransactionController::sum($person, $validated, $type);
        $this->assertEquals('35.75', $transaction);
    }

    public function test_sub_wallet()
    {
        $person = [
            'wallet' => '20.50'
        ];
        $validated = [
            'value' => '15.25'
        ];
        $type = '-';

        $transaction = TransactionController::sub($person, $validated, $type);
        $this->assertEquals('5.25', $transaction);
    }

}