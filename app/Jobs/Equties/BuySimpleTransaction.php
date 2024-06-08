<?php

namespace App\Jobs\Equties;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\SimpleTransaction;

class BuySimpleTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $order;

    private mixed $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct($transaction, $order)
    {
        $this->order = $order;
        $this->transaction = $transaction;
    }

    public function handle(): void
    {

        try {
            DB::beginTransaction();

            $transaction = $this->transaction;
            $order = $this->order;
            if (strtolower($transaction->category) == 'custodian') {
                $type = 'Custodian';
            } else {
                $type = 'Wallet';
            }

            $security = DB::table('securities')->find($order->security_id);
            $statement = new SimpleTransaction();
            $statement->client_id = $order->client_id;
            $statement->trans_id = $transaction->id;
            $statement->trans_category = $transaction->category;
            $statement->trans_reference = $transaction->reference;
            $statement->order_type = 'equity';
            $statement->order_id = $order->id;
            $statement->date = $transaction->transaction_date;
            $statement->type = $type;
            $statement->category = $transaction->category;
            $statement->reference = $order->uid;
            $statement->particulars = ' Purchase of '.$security->name.' shares';
            $statement->quantity = $order->executed;
            $statement->price = $order->price;
            $statement->debit = $order->payout;
            $statement->credit = 0;
            $statement->action = 'debit';
            $statement->amount = $order->payout;
            $statement->status = 'pending';
            $statement->save();
            DB::commit();
        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }
}
