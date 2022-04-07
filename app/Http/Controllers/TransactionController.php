<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\Client;

class TransactionController extends Controller
{
    public function index()
    {
        //
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        $payer = Client::where('id', $validated['payer'])->first();
        $payee = Client::where('id', $validated['payee'])->first();

        if(!isset($payer->id, $payee->id)){
            //usuario não foi encontrado
            return ['message' => 'pagante e(ou) recebedor não cadastrado(s)'];
        }elseif($payer->type == 1){
            //tipo "1" = lojista, não pode fazer transações
            return ['message' => 'lojista não pode efetuar transações'];
        }elseif($payer->wallet < $validated['value']){
            //valor insuficiente para a transação
            return ['message' => 'valor na carteira insuficiente'];
        }elseif($payer->id == $payee->id){
            //transação não pode ser feita entre a mesma pessoa
            return ['message' => 'valor na carteira insuficiente'];
        }

        $payer['wallet'] = $payer['wallet'] - $validated['value'];
        $payer->save();

        $payee['wallet'] = $payee['wallet'] + $validated['value'];
        $payee->save();
        
        $transaction = Transaction::create($validated);
        $transaction->save();
        return $transaction;
    }

    public function show(Transaction $transaction)
    {
        //
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $validated = $request->validated();

        $payer = Client::where('id', $transaction['payer'])->first();
        $payee = Client::where('id', $transaction['payee'])->first();

        if($validated['status'] == 1 && $transaction['status'] == 1){
            //se o status já está como 1(negado) não pode ser alterado novamente
            return ['message' => 'transação já está negada'];
        }else{
            //status 0 transação aprovada, status 1 transação negada
            $payer['wallet'] = $payer['wallet'] + $transaction['value'];
            $payer->save();
    
            $payee['wallet'] = $payee['wallet'] - $transaction['value'];
            $payee->save();
            
            $transaction['status'] = $validated['status'];
            $transaction->save();
            return $transaction;
        }
    }

    public function destroy(Transaction $transaction)
    {
        //
    }
}
