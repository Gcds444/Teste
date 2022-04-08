<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\Client;
use Mail;
use App\Mail\TransactionAcceptEmail;

class TransactionController extends Controller
{
    public function validation($payer, $payee, $validated)
    {
        $message = '';

        if(!isset($payer['id'], $payee['id'])){
            $message = 'pagante e(ou) recebedor não cadastrado(s)';       
        }elseif($payer['type'] == 1){
            $message = 'lojista não pode efetuar transações';
        }elseif($payer['wallet'] < $validated['value']){
            $message = 'valor na carteira insuficiente';
        }elseif($payer['id'] == $payee['id']){
            $message = 'transação não pode ser feita entre a mesma pessoa';
        }

        return ['message' => $message];
    }

    public function sum($person, $validated)
    {
        return $person['wallet'] + $validated['value'];
    } 

    public function sub($person, $validated)
    {
        return $person['wallet'] - $validated['value'];   
    }

    public function controlWallet($person, $validated, $type)
    {
        if($type == '+'){
            $person['wallet'] = $this->sum($person, $validated);
        }else{
            $person['wallet'] = $this->sub($person, $validated);
        }
            $person->save();
            return $person;
    }

    public function sendEmail(){
        try {
            //controller email
            //Mail::queue(new TransactionAcceptEmail($transaction));
            $response = file_get_contents('http://o4d9z.mocklab.io/notify');
            $response = json_decode($response, true);
            if($response['message'] !== 'Success'){            
                throw new Exception("Email não enviado", 500);  
            }
        } catch (\Throwable $th) {
            //throw deve ser enviado uma menssagem de erro para o sentry;
        }
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        $payer = Client::where('id', $validated['payer'])->first();
        $payee = Client::where('id', $validated['payee'])->first();

        $error = $this->validation($payer, $payee, $validated);

        if($error['message'] !== ''){
            return $error;
        }

        $response = file_get_contents('https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6');
        $response = json_decode($response, true);

        if($response['message'] !== 'Autorizado'){
            return ['message' => 'operação'];
        }

        $this->controlWallet($payer, $validated, '-');

        $this->controlWallet($payee, $validated, '+');

        $this->sendEmail();
        
        $transaction = Transaction::create($validated);
        $transaction->save();

        return $transaction;
           
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $validated = $request->validated();

        $payer = Client::where('id', $transaction['payer'])->first();
        $payee = Client::where('id', $transaction['payee'])->first();

        if($validated['status'] == 1 && $transaction['status'] == 1){
            return ['message' => 'transação já está negada'];
        }else{
            $this->controlWallet($payer, $transaction, '+');

            $this->controlWallet($payee, $transaction, '-');
            
            $transaction['status'] = $validated['status'];
            $transaction->save();
            return $transaction;
        }
    }
}
