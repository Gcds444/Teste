<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaction;
use App\Models\Client;

class TransactionAcceptEmail extends Mailable
{
    use Queueable, SerializesModels;
    private $transaction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $payer = Client::where('id', $this->transaction['payer'])->first();
        $payee = Client::where('id', $this->transaction['payee'])->first();

        return $this->view('emails.email_accept')
            ->to($payer)
            ->to($payee)
            ->subject('Confirmação da transação')
            ->with([
                'payer' => $payer,
                'payee' => $payee,
                'transaction' => $this->transaction,
            ]);
    }
}