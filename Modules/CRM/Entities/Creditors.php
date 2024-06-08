<?php

namespace Modules\CRM\Entities;

use App\Models\Accounting\Transaction;
use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Orders\Entities\Order;

class Creditors extends MasterModel
{
    use HasFactory;
    use SoftDeletes;
    use UuidForKey;

    protected $table = 'profiles';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'wallet_balance',
    ];

    public function getWalletBalanceAttribute(): float|int
    {
        $transactions = Transaction::where('status', 'approved')->groupBy('reference')
            ->where('client_id', $this->getAttribute('id'))->get();

        if (! empty($transactions)) {

            $balance = 0;

            foreach ($transactions as $transaction) {

                if (strtolower($transaction->category) == 'receipt') {
                    $balance += $transaction->amount;
                }

                if (strtolower($transaction->category) == 'payment') {
                    $balance -= $transaction->amount;
                }

                if (strtolower($transaction->category) == 'invoice') {
                    $balance -= $transaction->amount;
                }

                if (strtolower($transaction->category) == 'order') {

                    $order = Order::find($transaction->order_id);
                    if (strtolower($transaction->action) == 'debit') {
                        $balance -= $transaction->amount ?? 0;
                    } else {
                        $balance += $transaction->amount ?? 0;
                    }

                }

                if (strtolower($transaction->category) == 'expense') {
                    $balance += $transaction->amount;
                }

                if (strtolower($transaction->category) == 'journal') {
                    $balance -= $transaction->amount;
                }
            }

            return round($balance, 0);

        } else {
            return 0;
        }
    }
}
