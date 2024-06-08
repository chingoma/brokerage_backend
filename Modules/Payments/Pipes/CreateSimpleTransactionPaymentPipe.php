<?php

namespace Modules\Payments\Pipes;

use App\Models\Accounting\Transaction;
use Closure;
use Exception;
use Modules\Accounting\Entities\SimpleTransaction;

class CreateSimpleTransactionPaymentPipe
{
    /**
     * @throws Exception
     */
    public function handle(Transaction $transaction, Closure $next)
    {

        try {
            $transaction = self::__setTransaction($transaction);
            $statement = new SimpleTransaction();
            $statement->client_id = $transaction->client_id;
            $statement->trans_id = $transaction->trans_id;
            $statement->trans_category = $transaction->trans_category;
            $statement->trans_reference = $transaction->trans_reference;
            $statement->order_type = $transaction->order_type;
            $statement->order_id = $transaction->order_id;
            $statement->date = $transaction->raw_date;
            $statement->type = $transaction->type;
            $statement->category = $transaction->category;
            $statement->reference = $transaction->reference;
            $statement->particulars = $transaction->particulars;
            $statement->quantity = $transaction->quantity;
            $statement->price = $transaction->price;
            $statement->debit = $transaction->debit;
            $statement->credit = $transaction->credit;
            $statement->amount = $transaction->amount;
            $statement->action = 'debit';
            $statement->status = 'pending';
            $statement->save();

            return $next($statement);
        } catch (\Throwable $throwable) {
            report($throwable);
            throw new Exception($throwable->getMessage());
        }

    }

    private static function __setTransaction($transaction): \stdClass
    {

        $data = new \stdClass();

        $data->client_id = $transaction->client_id;
        $data->transaction_date = $transaction->transaction_date;
        $data->title = $transaction->title;
        $data->type = 'Wallet';
        $amount = $transaction->amount;
        $data->category = 'PAYMENT';
        $data->reference = $transaction->uid;
        $data->amount = $amount;
        $data->order_type = '';
        $data->order_id = '';
        $data->trans_id = $transaction->id;
        $data->trans_reference = $transaction->reference;
        $data->trans_category = $transaction->category;
        $data->date = date('Y-m-d', strtotime($transaction->transaction_date));
        $data->raw_date = $transaction->transaction_date;
        $data->type = strtoupper($data->type);
        $data->particulars = strtoupper($data->title);
        $data->quantity = 0;
        $data->price = 0;
        $data->debit = $amount;
        $data->credit = 0;
        $data->action = 'debit';

        return $data;
    }
}
