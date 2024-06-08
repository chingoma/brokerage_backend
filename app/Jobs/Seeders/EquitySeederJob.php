<?php

namespace App\Jobs\Seeders;

use App\Helpers\EquitiesHelper;
use App\Helpers\Helper;
use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use App\Models\Security;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\SimpleTransaction;
use Modules\Accounting\Helpers\AccountingHelper;
use Modules\Orders\Entities\Order;
use Modules\Wallet\Entities\EquitiesOnHold;
use Modules\Wallet\Entities\Wallet;
use Throwable;

class EquitySeederJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {

        try {
            for ($i = 0; $i < 1; $i++) {
                $price = random_int(500, 1550);
                $volume = random_int(1000, 2000);
                $order = new Order();
                $systemDate = Helper::systemDateTime();
                $company = fake()->shuffle(Security::get()->pluck('id')->toArray())[0];
                $category = DB::table('customer_categories')->where('id', $this->user->category_id)->first();
                $scheme = DB::table('equity_schemes')->where('id', $category->equity_scheme)->first();

                $order->client_id = $this->user->id;
                $order->type = 'buy';
                $order->date = $systemDate['timely'];
                $order->price = $price;
                $order->volume = $volume;
                $order->balance = 0;
                $order->status = 'complete';
                $order->closed = 'yes';
                $order->amount = $order->price * $order->volume;
                $order->security_id = $company;
                $order->mode = 'market';
                $order->financial_year_id = Helper::business()->financial_year;
                $rates = EquitiesHelper::commissionRates($scheme);
                $order->rate_step_one = $rates['one'];
                $order->rate_step_two = $rates['two'];
                $order->rate_step_three = $rates['three'];
                $order->brokerage_rate = $scheme->flat_rate;
                EquitiesHelper::setCommissions($order);
                $order->save();
                Helper::orderUID($order);

                $orderData = $order;
                $orderI = new DealingSheet();
                $orderI->settlement_date = Helper::settlementDateEquity($systemDate['today']);
                $orderI->trade_date = $systemDate['timely'];
                $orderI->volume = $orderData->volume;
                $orderI->other_charges = 0;
                $orderI->slip_no = AccountingHelper::generateReference();
                $orderI->balance = 0;
                $orderI->price = $price;
                $orderI->amount = $price * $volume;
                $orderI->status = 'approved';
                $orderI->executed = $volume;
                $orderI->mode = 'market';
                $orderI->type = 'buy';
                $orderI->order_id = $orderData->id;
                $orderI->client_id = $orderData->client_id;
                $orderI->security_id = $orderData->security_id;
                $orderI->financial_year_id = Helper::business()->financial_year;
                $rates = EquitiesHelper::commissionRates($scheme);
                $orderI->rate_step_one = $rates['one'];
                $orderI->rate_step_two = $rates['two'];
                $orderI->rate_step_three = $rates['three'];
                $orderI->brokerage_rate = $scheme->flat_rate;
                EquitiesHelper::setCommissions($orderI);
                $order->save();
                Helper::dealingSheetUID($orderI);
                EquitiesHelper::_process_order_buy($orderI);

                //    Transaction::update(['status' => "approved"]);
                Statement::update(['status' => 'approved']);
                EquitiesOnHold::truncate();
                SimpleTransaction::update(['status' => 'approved']);
                $transaction = SimpleTransaction::where('order_type', 'equity')
                    ->latest('id')
                    ->where('client_id', $this->user->id)
                    ->first();
                $lastEntry = Statement::where('client_id', $this->user->id)
                    ->latest('auto')
                    ->limit(1)
                    ->first();

                $statement = new Statement();
                $statement->status = 'approved';
                $statement->trans_id = $transaction->trans_id;
                $statement->trans_category = $transaction->trans_category;
                $statement->trans_reference = $transaction->trans_reference;
                $statement->order_type = $transaction->order_type;
                $statement->order_id = $transaction->order_id;
                $statement->client_id = $transaction->client_id;
                $statement->date = $transaction->date;
                $statement->type = $transaction->type;
                $statement->category = $transaction->category;
                $statement->reference = $transaction->reference;
                $statement->particulars = $transaction->particulars;
                $statement->quantity = $transaction->quantity;
                $statement->price = $transaction->price;
                $statement->debit = $transaction->debit;
                $statement->credit = $transaction->credit;

                if (empty($lastEntry)) {
                    $balance = 0;
                } else {
                    $balance = $lastEntry->balance;
                }

                if (strtolower($transaction->action) == 'credit') {
                    $statement->balance = $balance + $transaction->amount;
                } else {
                    $statement->balance = $balance - $transaction->amount;
                }

                if ($statement->balance < 0) {
                    $state = 'Dr';
                } else {
                    $state = 'Cr';
                }

                $statement->state = $state;
                $statement->save();

                $wallet = Wallet::first(['user_id' => $this->user->id]);
                $wallet->actual_balance = $wallet->actual_balance - $statement->balance;
                $wallet->available_balance = $wallet->available_balance - $statement->balance;
                $wallet->save();
            }

        } catch (Throwable $throwable) {
            report($throwable);
        }

    }
}
