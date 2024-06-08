<?php

namespace App\Jobs\Statements;

use App\Mail\CustomEmail;
use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\Accounting\Entities\SimpleTransaction;

class UpdateCustomerWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct(SimpleTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $lastEntry = Statement::where('client_id', $this->transaction->client_id)
                ->latest('auto')
                ->limit(1)
                ->first();

            $statement = new Statement();
            $statement->trans_id = $this->transaction->trans_id;
            $statement->trans_category = $this->transaction->trans_category;
            $statement->trans_reference = $this->transaction->trans_reference;
            $statement->order_type = $this->transaction->order_type;
            $statement->order_id = $this->transaction->order_id;
            $statement->client_id = $this->transaction->client_id;
            $statement->date = $this->transaction->date;
            $statement->type = $this->transaction->type;
            $statement->category = $this->transaction->category;
            $statement->reference = $this->transaction->reference;
            $statement->particulars = $this->transaction->particulars;
            $statement->quantity = $this->transaction->quantity;
            $statement->price = $this->transaction->price;
            $statement->debit = $this->transaction->debit;
            $statement->credit = $this->transaction->credit;

            if (empty($lastEntry)) {
                $balance = 0;
            } else {
                if (strtolower($lastEntry->state) == 'cr') {
                    $balance = $lastEntry->balance;
                } else {
                    $balance = -1 * $lastEntry->balance;
                }
            }

            if (strtolower($this->transaction->type) == 'custodian') {
                $statement->balance = $balance;
            } else {
                if (strtolower($this->transaction->action) == 'credit') {
                    $statement->balance = $balance + $this->transaction->amount;
                } else {
                    $statement->balance = $balance - $this->transaction->amount;
                }
            }

            if ($statement->balance < 0) {
                $state = 'Dr';
            } else {
                $state = 'Cr';
            }
            $statement->balance = abs($statement->balance);
            $statement->state = $state;
            $statement->save();

        } catch (\Exception $exception) {
            report($exception);
            $mailable = new CustomEmail('LOCKMINDS - Statement Update Failed ', " failed to complete statement update \n Exception message \n ".$exception->getMessage());
            Mail::to('canwork.job@gmail.com')->queue($mailable);
        }
    }
}
