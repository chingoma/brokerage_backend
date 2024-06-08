<?php

namespace Database\Factories;

use App\Helpers\EquitiesHelper;
use App\Helpers\Helper;
use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\Transaction;
use App\Models\Bank;
use App\Models\DealingSheet;
use App\Models\PaymentMethod;
use App\Models\Security;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\SimpleTransaction;
use Modules\Accounting\Helpers\AccountingHelper;
use Modules\CRM\Entities\CustomerCategory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */

use Modules\Orders\Entities\Order;
use Modules\Receipts\DTOs\CreateReceiptTransactionDTO;
use Modules\Receipts\Entities\Receipt;
use Modules\Receipts\Pipes\CreateReceiptPipe;
use Modules\Receipts\Pipes\CreateReceiptTransactionPipe;
use Modules\Receipts\Pipes\CreateSimpleTransactionReceiptPipe;
use Modules\Wallet\Entities\EquitiesOnHold;
use Modules\Wallet\Entities\Wallet;
use stdClass;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->shuffleArray(['individual'])[0];
        $risks = ['PEP', 'High Risk', 'Low Risk'];
        $sexes = ['male', 'female'];
        $banks = Bank::get()->pluck('id')->toArray();
        $categories = CustomerCategory::get()->pluck('id')->toArray();
        $firstname = fake()->firstName(fake()->shuffleArray($sexes)[0]);
        $lastname = fake()->firstName(fake()->shuffleArray($sexes)[0]);

        return [
            'risk_status' => fake()->shuffleArray($risks)[0],
            'flex_acc_no' => fake()->shuffle('1234567890'),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'name' => $firstname.' '.$lastname,
            'email' => fake()->unique()->companyEmail(),
            'mobile' => fake()->phoneNumber(),
            'status' => 'active',
            'type' => $type,
            'email_verified_at' => now()->toDateTimeString(),
            'password' => fake()->password(),
            'self_registration' => false,
            'dse_account' => fake()->shuffle('120457'),
            'bank_id' => fake()->shuffleArray($banks)[0],
            'bank_account_name' => $firstname.' '.$lastname,
            'bank_account_number' => fake()->shuffle('1204578923600'),
            'bank_branch' => fake()->company(),
            'manager_id' => User::first()->id,
            'category_id' => fake()->shuffleArray($categories)[0],
            'is_admin' => false,
            'has_custodian' => 'no',
            'custodian_approved' => 'no',
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            // ...
        })->afterCreating(function (User $user) {
            Helper::customerUID($user);
            $data = new stdClass();
            $paymentMethod = PaymentMethod::first()->id;
            $category = AccountCategory::where('type', 'receipt')->first()->id;
            $data->amount = random_int(500000000, 1000000000);
            $data->payment_method = $paymentMethod;
            $data->payee = $user->id;
            $data->category = $category;
            $data->description = 'Opening balance';
            $requestData = CreateReceiptTransactionDTO::fromJson(json_encode($data));
            $pipes = [
                CreateReceiptTransactionPipe::class,
                CreateReceiptPipe::class,
                CreateSimpleTransactionReceiptPipe::class,
            ];

            app(Pipeline::class)
                ->send($requestData)
                ->through($pipes)
                ->then(function ($simpleTransaction) {
                    return $simpleTransaction;
                });

            Transaction::where('client_id', $user->id)->update(['status' => 'approved']);

            $transaction = SimpleTransaction::where('client_id', $user->id)->first();
            $transaction->status = 'approved';
            $transaction->save();

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
            $statement->balance = $transaction->amount;
            $state = 'Cr';
            $statement->state = $state;
            $statement->save();

            $receipt = Receipt::where('client_id', $user->id)->first();
            $receipt->status = 'approved';
            $receipt->save();

            $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
            $wallet->actual_balance = $transaction->amount;
            $wallet->available_balance = $transaction->amount;
            $wallet->save();

            for ($i = 0; $i < 100; $i++) {
                $price = random_int(500, 1550);
                $volume = random_int(1000, 2000);
                $order = new Order();
                $systemDate = Helper::systemDateTime();
                $company = fake()->shuffle(Security::get()->pluck('id')->toArray())[0];
                $category = DB::table('customer_categories')->where('id', $user->category_id)->first();
                $scheme = DB::table('equity_schemes')->where('id', $category->equity_scheme)->first();

                $order->client_id = $user->id;
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
                $orderI->save();
                Helper::dealingSheetUID($orderI);
                EquitiesHelper::_process_order_buy($orderI);
                Transaction::where('reference', $orderI->slip_no)->update(['status' => 'approved']);
                Statement::where('client_id', $user->id)->update(['status' => 'approved']);
                EquitiesOnHold::truncate();
                SimpleTransaction::where('client_id', $user->id)->update(['status' => 'approved']);
                $transaction = SimpleTransaction::where('order_type', 'equity')
                    ->latest('id')
                    ->where('client_id', $user->id)
                    ->first();
                $lastEntry = Statement::where('client_id', $user->id)
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
                    if ($transaction->amount < 0) {
                        $statement->balance = $balance + $transaction->amount;
                    } else {
                        $statement->balance = $balance - $transaction->amount;
                    }

                }

                if ($statement->balance < 0) {
                    $state = 'Dr';
                } else {
                    $state = 'Cr';
                }

                $statement->state = $state;
                $statement->save();

                $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
                $wallet->actual_balance = $statement->balance;
                $wallet->available_balance = $statement->balance;
                $wallet->save();
            }
        });
    }
}
