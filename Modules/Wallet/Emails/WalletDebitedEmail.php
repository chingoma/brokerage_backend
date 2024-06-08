<?php

namespace Modules\Wallet\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WalletDebitedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public mixed $name = '';

    public mixed $amount = '';

    public mixed $account = '';

    public mixed $remark = '';

    public mixed $datetime = '';

    public mixed $balance = '';

    /**
     * @param  mixed|string  $name
     * @param  mixed|string  $amount
     * @param  mixed|string  $account
     * @param  mixed|string  $remark
     * @param  mixed|string  $datetime
     * @param  mixed|string  $balance
     */
    public function __construct(mixed $name, mixed $amount, mixed $account, mixed $remark, mixed $datetime, mixed $balance)
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->account = $account;
        $this->remark = $remark;
        $this->datetime = $datetime;
        $this->balance = $balance;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject('Wallet debited')->tag('wallet-debited')->markdown('wallet::emails.wallet_debit');
    }
}
