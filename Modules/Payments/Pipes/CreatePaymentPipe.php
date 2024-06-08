<?php

namespace Modules\Payments\Pipes;

use App\Models\Accounting\Transaction;
use Closure;
use Exception;
use Modules\Payments\Entities\Payment;
use Modules\Transactions\Http\Controllers\TransactionsController;
use Modules\Wallet\Entities\AvailableWalletHistory;
use Modules\Wallet\Entities\PaymentsOnHold;

class CreatePaymentPipe
{
    /**
     * @throws Exception
     */
    public function handle(Transaction $transaction, Closure $next)
    {

        try {

            $payment = new Payment();
            $payment->uid = $transaction->uid;
            $payment->trans_id = $transaction->id;
            $payment->reference = $transaction->reference;
            $payment->client_id = $transaction->client_id;
            $payment->date = $transaction->transaction_date;
            $payment->particulars = $transaction->title;
            $payment->amount = $transaction->amount;
            $payment->save();

            $paymentOnHold = new PaymentsOnHold();
            $paymentOnHold->amount = $payment->amount;
            $paymentOnHold->user_id = $payment->client_id;
            $paymentOnHold->payment_id = $payment->id;
            $paymentOnHold->save();

            $history = new AvailableWalletHistory();
            $history->user_id = $payment->client_id;
            $history->model_id = $payment->id;
            $history->category = 'payment';
            $history->amount = $payment->amount;
            $history->description = 'Decrease available balance';
            $history->save();

            TransactionsController::updateWallet($paymentOnHold->user_id);

            return $next($transaction);
        } catch (\Throwable $throwable) {
            report($throwable);
            throw new Exception($throwable->getMessage());
        }
    }
}
