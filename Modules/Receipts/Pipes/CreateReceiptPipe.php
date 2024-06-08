<?php

namespace Modules\Receipts\Pipes;

use App\Models\Accounting\Transaction;
use Closure;
use Exception;
use Modules\Receipts\Entities\Receipt;

class CreateReceiptPipe
{
    /**
     * @throws Exception
     */
    public function handle(Transaction $transaction, Closure $next)
    {

        try {
            $receipt = new Receipt();
            $receipt->uid = $transaction->uid;
            $receipt->trans_id = $transaction->id;
            $receipt->reference = $transaction->reference;
            $receipt->client_id = $transaction->client_id;
            $receipt->date = $transaction->transaction_date;
            $receipt->particulars = $transaction->title;
            $receipt->amount = $transaction->amount;
            $receipt->save();

            return $next($transaction);
        } catch (\Throwable $throwable) {
            report($throwable);
            throw new Exception($throwable->getMessage());
        }
    }
}
