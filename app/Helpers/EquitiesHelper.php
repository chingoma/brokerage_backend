<?php

// Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Models\Accounting\AccountSetting;
use App\Models\Accounting\Transaction;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\SimpleTransaction;
use Modules\Orders\Entities\Order;
use Modules\Transactions\Http\Controllers\TransactionsController;
use Modules\Wallet\Entities\AvailableWalletHistory;
use Modules\Wallet\Entities\EquitiesOnHold;

class EquitiesHelper
{
    public static function brokerageCommissions($dealingSheet): array
    {
        $user = \DB::table('users')->find($dealingSheet->client_id);
        $category = \DB::table('customer_categories')->find($user->category_id);
        $scheme = \DB::table('equity_schemes')->find($category->equity_scheme);
        $amount = $dealingSheet->amount;
        $balance = $amount;
        $totalCommissionRates = self::brokerCommissionsTotal($scheme, $amount);
        $stepOneCommission = 0;
        $stepTwoCommission = 0;
        $stepThreeCommission = 0;

        if ($dealingSheet->use_custom_commission == 'yes') {
            if ($dealingSheet->use_flat == 'yes') {
                $totalCommissions = round($amount * floatval($dealingSheet->brokerage_rate), 2);
            } else {
                // Step 1
                if ($balance > 10000000) {
                    $stepOneCommission = self::commissionStep1Custom(10000000, $dealingSheet->rate_step_one);
                    $balance = $amount - 10000000;
                } else {
                    $stepOneCommission = self::commissionStep1Custom($amount, $dealingSheet->rate_step_one);
                    $balance = 0;
                }

                // Step 2
                if ($balance > 0) {
                    if ($balance > 40000000) {
                        $stepTwoCommission = self::commissionStep2Custom(40000000, $dealingSheet->rate_step_two);
                        $balance = $amount - 50000000;
                    } else {
                        $stepTwoCommission = self::commissionStep2Custom($balance, $dealingSheet->rate_step_two);
                        $balance = 0;
                    }
                } else {
                    $balance = 0;
                }

                // Step 3
                if ($balance > 0) {
                    $stepThreeCommission = self::commissionStep3Custom($balance, $dealingSheet->rate_step_three);
                }

                $totalCommissions = $stepOneCommission + $stepTwoCommission + $stepThreeCommission;
            }
        } else {
            if (strtolower($scheme->mode) != 'default') {
                $totalCommissions = round($amount * floatval($scheme->flat_rate), 2);
            } else {
                // Step 1
                if ($balance > 10000000) {
                    $stepOneCommission = self::commissionStep1($scheme, 10000000);
                    $balance = $amount - 10000000;
                } else {
                    $stepOneCommission = self::commissionStep1($scheme, $amount);
                    $balance = 0;
                }

                // Step 2
                if ($balance > 0) {
                    if ($balance > 40000000) {
                        $stepTwoCommission = self::commissionStep2($scheme, 40000000);
                        $balance = $amount - 50000000;
                    } else {
                        $stepTwoCommission = self::commissionStep2($scheme, $balance);
                        $balance = 0;
                    }
                } else {
                    $balance = 0;
                }

                // Step 3
                if ($balance > 0) {
                    $stepThreeCommission = self::commissionStep3($scheme, $balance);
                }

                $totalCommissions = $stepOneCommission + $stepTwoCommission + $stepThreeCommission;
            }
        }

        $vat = $totalCommissions * 0.18;
        $grandCommissions = $vat + $totalCommissions + $totalCommissionRates;

        $result['brokerage'] = $totalCommissions;
        $result['fees'] = self::brokerCommissions($scheme, $amount);
        $result['total_fees'] = $totalCommissionRates;
        $result['vat'] = $vat;
        $result['total'] = $grandCommissions;
        $result['step_one'] = $stepOneCommission;
        $result['step_two'] = $stepTwoCommission;
        $result['step_three'] = $stepThreeCommission;

        return $result;
    }

    public static function brokerRates($scheme): array
    {
        $data = [];
        $data['cmsa'] = $scheme->cmsa_fee;
        $data['dse'] = $scheme->dse_fee;
        $data['fidelity'] = $scheme->fidelity_fee;
        $data['cds'] = $scheme->csdr_fee;
        $data['vat'] = 0.18;

        return $data;
    }

    public static function commissionRates($scheme): array
    {
        $rates['one'] = $scheme->step_one;
        $rates['two'] = $scheme->step_two;
        $rates['three'] = $scheme->step_three;

        return $rates;
    }

    public static function commissionStep1($scheme, $amount): float
    {
        $rates = self::commissionRates($scheme);

        return round($amount * $rates['one'], 4);
    }

    public static function commissionStep2($scheme, $amount): float
    {
        $rates = self::commissionRates($scheme);

        return round($amount * $rates['two'], 4);
    }

    public static function commissionStep3($scheme, $amount): float
    {
        $rates = self::commissionRates($scheme);

        return round($amount * $rates['three'], 4);
    }

    public static function commissionStep1Custom($amount, $rate): float
    {
        return round($amount * $rate, 4);
    }

    public static function commissionStep2Custom($amount, $rate): float
    {
        return round($amount * $rate, 4);
    }

    public static function commissionStep3Custom($amount, $rate): float
    {
        return round($amount * $rate, 4);
    }

    public static function brokerCommissions($scheme, $amount): array
    {
        $rates = self::brokerRates($scheme);
        $data['cmsa'] = round($rates['cmsa'] * $amount, 4);
        $data['dse'] = round($rates['dse'] * $amount, 4);
        $data['fidelity'] = round($rates['fidelity'] * $amount, 4);
        $data['cds'] = $rates['cds'] * $amount;

        return $data;
    }

    public static function brokerCommissionsTotal($scheme, $amount): float
    {
        $rates = self::brokerRates($scheme);
        $data['cmsa'] = $rates['cmsa'] * $amount;
        $data['dse'] = $rates['dse'] * $amount;
        $data['fidelity'] = $rates['fidelity'] * $amount;
        $data['cds'] = $rates['cds'] * $amount;

        return round($data['cmsa'], 4) + round($data['dse'], 4) + round($data['fidelity'], 4) + round($data['cds'], 4);
    }

    public static function setCommissions($dealingSheet): void
    {
        $commissions = EquitiesHelper::brokerageCommissions($dealingSheet);
        $fees = $commissions['fees'];
        $dealingSheet->commission_step_one = $commissions['step_one'];
        $dealingSheet->commission_step_two = $commissions['step_two'];
        $dealingSheet->commission_step_three = $commissions['step_three'];
        $dealingSheet->dse = $fees['dse'];
        $dealingSheet->cmsa = $fees['cmsa'];
        $dealingSheet->fidelity = $fees['fidelity'];
        $dealingSheet->cds = $fees['cds'];
        $dealingSheet->total_fees = $commissions['total'];
        $dealingSheet->vat = $commissions['vat'];
        $dealingSheet->brokerage = $commissions['brokerage'];
        $dealingSheet->total_commissions = $commissions['total_fees'];

        if (strtolower($dealingSheet->type) == 'buy') {
            $dealingSheet->payout = $dealingSheet->amount + $commissions['total'] + $dealingSheet->other_charges;
        } else {
            $dealingSheet->payout = $dealingSheet->amount - $commissions['total'] - $dealingSheet->other_charges;
        }
    }

    public static function clearFees($dealingSheet): void
    {
        Transaction::where('reference', $dealingSheet->slip_no)->delete();
        $dealingSheet->commission_step_one = 0;
        $dealingSheet->commission_step_two = 0;
        $dealingSheet->commission_step_three = 0;
        $dealingSheet->dse = 0;
        $dealingSheet->cmsa = 0;
        $dealingSheet->fidelity = 0;
        $dealingSheet->cds = 0;
        $dealingSheet->total_fees = 0;
        $dealingSheet->vat = 0;
        $dealingSheet->brokerage = 0;
        $dealingSheet->total_commissions = 0;
        $dealingSheet->payout = 0;
    }

    public static function _process_order_buy($order): void
    {
        $orderData = Order::find($order->order_id);
        $setting = AccountSetting::first();
        $category = ($orderData->has_custodian == 'yes') ? 'Custodian' : 'Order';

        Transaction::where('reference', $order->slip_no)->delete();
        // reduce liability for customer because customer deposited cash for buying shares
        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->transaction_date = $order->trade_date;
        $transaction->title = 'Buying of Shares '.$order->security->name;
        $transaction->amount = $order->amount + $order->total_fees;
        $transaction->status = 'Pending';
        $transaction->action = 'Debit';
        $transaction->category = $category;
        $transaction->customer_action = 'Withdraw';
        $transaction->debit = $order->amount + $order->total_fees;
        $transaction->credit = 0;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->account_id = Helper::account($setting->order_liability_account)->id;
        $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $orderData->client_id;
        $transaction->save();
        Helper::transactionUID($transaction);
        $uid = $transaction->uid;

        $simpleTransaction = $transaction;
        self::__buySimpleTransaction($simpleTransaction, $order);
        // reduce cash because we have used cash to buy shares
        $transaction = new Transaction();
        $transaction->uid = $uid;
        $transaction->external_reference = $order->uid;
        $transaction->transaction_date = $order->trade_date;
        $transaction->title = 'Buying of Shares'.$order->security->name;
        $transaction->amount = $order->amount + $order->total_fees;
        $transaction->status = 'Pending';
        $transaction->debit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->credit = $order->amount + $order->total_fees;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        if (strtolower($category) == 'custodian') {
            $transaction->custodian_id = $orderData->custodian_id;
            $transaction->account_id = $setting->order_liability_account;
            //            $transaction->account_id = $setting->custodian_account;
            $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
            //            $transaction->class_id = Helper::account($setting->custodian_account)->class_id;
        } else {
            $transaction->account_id = $setting->order_liability_account;
            $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
        }
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $orderData->client_id;
        $transaction->save();
        self::_recordRevenueAndCommissions($order, $uid);
    }

    public static function _process_order_sell($order): void
    {
        $orderData = Order::find($order->order_id);
        $setting = AccountSetting::first();
        $category = ($orderData->has_custodian == 'yes') ? 'Custodian' : 'Order';
        Transaction::where('reference', $order->slip_no)->delete();
        // reduce liability for customer because customer deposited cash for buying shares
        $transaction = new Transaction();
        $transaction->title = 'Selling of Shares'.$order->security->name;
        $transaction->transaction_date = $order->trade_date;
        $transaction->amount = $order->amount - $order->total_fees;
        $transaction->status = 'Pending';
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->customer_action = 'Deposit';
        $transaction->credit = $order->amount - $order->total_fees;
        $transaction->debit = 0;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        if (strtolower($category) == 'custodian') {
            //            $transaction->custodian_id = $orderData->custodian_id;
            $transaction->account_id = $setting->order_liability_account;
            //            $transaction->account_id = $setting->custodian_account;
            $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
            //            $transaction->class_id = Helper::account($setting->custodian_account)->class_id;
        } else {
            $transaction->account_id = $setting->order_liability_account;
            $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
        }
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $orderData->client_id;
        $transaction->save();
        Helper::transactionUID($transaction);
        $uid = $transaction->uid;

        $simpleTransaction = $transaction;
        self::__saleSimpleTransaction($simpleTransaction, $order);

        // reduce cash because we have used cash to buy shares
        $transaction = new Transaction();
        $transaction->uid = $uid;
        $transaction->transaction_date = $order->trade_date;
        $transaction->title = 'Selling of Shares '.$order->security->name;
        $transaction->amount = $order->amount - $order->total_fees;
        $transaction->status = 'Pending';
        $transaction->credit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->debit = $order->amount - $order->total_fees;
        $transaction->reference = $order->slip_no;
        if ($category == 'Custodian') {
            $transaction->account_id = $setting->order_liability_account;
            //            $transaction->account_id = $setting->custodian_account;
            $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
            //            $transaction->class_id = Helper::account($setting->custodian_account)->class_id;
        } else {
            $transaction->account_id = $setting->order_liability_account;
            $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
        }
        $transaction->cash_account = 'yes';
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $orderData->client_id;
        $transaction->save();

        self::_recordRevenueAndCommissions($order, $uid);
    }

    public static function _recordRevenueAndCommissions($order, $uid)
    {
        $orderData = Order::find($order->order_id);
        $setting = AccountSetting::first();

        $category = ($orderData->has_custodian == 'yes') ? 'Custodian' : 'Order';
        // record fees
        if ($order->other_charges > 0) {
            $transaction = new Transaction();
            $transaction->external_reference = $order->uid;
            $transaction->transaction_date = $order->trade_date;
            $transaction->client_id = $order->client_id;
            $transaction->amount = $order->other_charges;
            $transaction->status = 'Pending';
            $transaction->debit = 0;
            $transaction->action = 'Credit';
            $transaction->category = $category;
            $transaction->credit = $order->other_charges;
            $transaction->reference = $order->slip_no;
            $transaction->description = 'Processing of an order';
            $transaction->title = 'Processing of an order';
            $transaction->account_id = Helper::account($setting->order_liability_account)->id;
            $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
            $transaction->order_id = $orderData->id;
            $transaction->financial_year_id = Helper::business()->financial_year;
            $transaction->uid = $uid;
            $transaction->save();

            $transaction = new Transaction();
            $transaction->external_reference = $order->uid;
            $transaction->transaction_date = $order->trade_date;
            $transaction->client_id = $orderData->client_id;
            $transaction->amount = $order->other_charges;
            $transaction->status = 'Pending';
            $transaction->credit = 0;
            $transaction->action = 'Debit';
            $transaction->category = $category;
            $transaction->debit = $order->other_charges;
            $transaction->reference = $order->slip_no;
            $transaction->description = 'Processing of an order';
            $transaction->title = 'Processing of an order';

            $transaction->account_id = Helper::account($setting->order_cash_account)->id;
            $transaction->class_id = Helper::account($setting->order_cash_account)->class_id;

            $transaction->order_id = $orderData->id;
            $transaction->financial_year_id = Helper::business()->financial_year;
            $transaction->uid = $uid;
            $transaction->save();

        }
        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->transaction_date = $order->trade_date;
        $transaction->client_id = $setting->dse_payee_account;
        $transaction->amount = $order->dse;
        $transaction->status = 'Pending';
        $transaction->debit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->credit = $order->dse;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'DSE Fee';
        $transaction->title = 'DSE Fee';
        $transaction->account_id = Helper::account($setting->dse_fee_account)->id;
        $transaction->class_id = Helper::account($setting->dse_fee_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->uid = $uid;
        $transaction->save();

        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->amount = $order->cmsa;
        $transaction->status = 'Pending';
        $transaction->debit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->credit = $order->cmsa;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->title = 'CMSA Fee';
        $transaction->account_id = Helper::account($setting->cmsa_fee_account)->id;
        $transaction->class_id = Helper::account($setting->cmsa_fee_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->transaction_date = $order->trade_date;
        $transaction->client_id = $setting->cmsa_payee_account;
        $transaction->uid = $uid;
        $transaction->save();

        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->amount = $order->fidelity;
        $transaction->status = 'Pending';
        $transaction->debit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->credit = $order->fidelity;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->title = 'Fidelity Fee';
        $transaction->account_id = Helper::account($setting->fidelity_fee_account)->id;
        $transaction->class_id = Helper::account($setting->fidelity_fee_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->transaction_date = $order->trade_date;
        $transaction->client_id = $setting->fidelity_payee_account;
        $transaction->uid = $uid;
        $transaction->save();

        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->amount = $order->cds;
        $transaction->status = 'Pending';
        $transaction->debit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->credit = $order->cds;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->title = 'CDS Fee';
        $transaction->account_id = Helper::account($setting->cds_fee_account)->id;
        $transaction->class_id = Helper::account($setting->cds_fee_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->transaction_date = $order->trade_date;
        $transaction->client_id = $setting->cds_payee_account;
        $transaction->uid = $uid;
        $transaction->save();

        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->amount = $order->vat;
        $transaction->status = 'Pending';
        $transaction->debit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->credit = $order->vat;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->title = 'VAT';
        $transaction->vat_type = 'output';
        $transaction->account_id = Helper::account($setting->vat_account)->id;
        $transaction->class_id = Helper::account($setting->vat_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->transaction_date = $order->trade_date;
        $transaction->client_id = $setting->vat_payee_account;
        $transaction->uid = $uid;
        $transaction->save();

        // Record brokerage revenue
        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->amount = $order->brokerage;
        $transaction->status = 'Pending';
        $transaction->debit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->credit = $order->brokerage;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->title = 'Processing of an order';
        $transaction->account_id = Helper::account($setting->order_revenue_account)->id;
        $transaction->class_id = Helper::account($setting->order_revenue_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->transaction_date = $order->trade_date;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $orderData->client_id;
        $transaction->uid = $uid;
        $transaction->save();

        // Record cash
        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->amount = $order->amount;
        $transaction->status = 'Pending';
        $transaction->credit = 0;
        $transaction->cash_account = 'yes';
        $transaction->action = 'Debit';
        $transaction->category = $category;
        $transaction->debit = $order->brokerage;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->title = 'Processing of an order';
        $transaction->account_id = Helper::account($setting->order_cash_account)->id;
        $transaction->class_id = Helper::account($setting->order_cash_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $orderData->client_id;
        $transaction->transaction_date = $order->trade_date;
        $transaction->uid = $uid;
        $transaction->save();

        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->amount = $order->total_commissions;
        $transaction->status = 'Pending';
        $transaction->credit = 0;
        $transaction->action = 'Debit';
        $transaction->category = $category;
        $transaction->cash_account = 'yes';
        $transaction->debit = $order->total_commissions;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->account_id = Helper::account($setting->order_cash_account)->id;
        $transaction->class_id = Helper::account($setting->order_cash_account)->class_id;
        $transaction->order_id = $orderData->id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $orderData->client_id;
        $transaction->transaction_date = $order->trade_date;
        $transaction->uid = $uid;
        $transaction->save();

        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->amount = $order->vat;
        $transaction->status = 'Pending';
        $transaction->credit = 0;
        $transaction->action = 'Debit';
        $transaction->category = $category;
        $transaction->debit = $order->vat;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing of an order';
        $transaction->account_id = Helper::account($setting->order_cash_account)->id;
        $transaction->class_id = Helper::account($setting->order_cash_account)->class_id;
        $transaction->cash_account = 'yes';
        $transaction->order_id = $orderData->id;
        $transaction->transaction_date = $order->trade_date;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $orderData->client_id;
        $transaction->uid = $uid;
        $transaction->save();
    }

    private static function __buySimpleTransaction(Transaction $transaction, $order): void
    {
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
        $statement->category = 'PURCHASE';
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
    }

    private static function __saleSimpleTransaction(Transaction $transaction, $order): void
    {
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
        $statement->category = 'SALE';
        $statement->reference = $order->uid;
        $statement->particulars = ' Sale of '.$security->name.' shares';
        $statement->quantity = $order->executed;
        $statement->price = $order->price;
        $statement->credit = $order->payout;
        $statement->debit = 0;
        $statement->action = 'credit';
        $statement->amount = $order->payout;
        $statement->status = 'pending';
        $statement->save();
    }

    public static function updateOrderStatus(string $id): void
    {
        $order = Order::find($id);
        $executed = DB::table('dealing_sheets')
            ->whereNull("deleted_at")
            ->where('status', '!=', 'cancelled')
            ->where('order_id', $id)
            ->sum('executed');
        $difference = $order->volume - $executed;
        if ($difference <= 0) {
            $order->status = 'complete';
            $order->closed = 'yes';
            EquitiesOnHold::where('equity_id', $order->id)->delete();
        }
        if ($difference > 0) {
            $order->closed = 'no';
            $order->status = 'approved';
        }
        $order->balance = $difference;
        $order->save();
    }

    public static function updateWallet($order): void
    {
        if (strtolower($order->type) == 'buy') {
            $onHold = new EquitiesOnHold();
            $onHold->amount = $order->payout;
            $onHold->user_id = $order->client_id;
            $onHold->equity_id = $order->id;
            $onHold->save();

            $history = new AvailableWalletHistory();
            $history->user_id = $order->client_id;
            $history->model_id = $order->id;
            $history->category = 'equity';
            $history->amount = $order->payout;
            $history->description = 'Decrease available balance';
            $history->save();

            TransactionsController::updateWallet($onHold->user_id);

        }
    }

    public static function updateWalletAfterReject($order): void
    {
        if (strtolower($order->type) == 'buy') {
            $history = new AvailableWalletHistory();
            $history->user_id = $order->client_id;
            $history->model_id = $order->id;
            $history->category = 'equity';
            $history->amount = $order->payout;
            $history->description = 'Increase available balance after order reject';
            $history->save();

            TransactionsController::updateWallet($order->client_id);

        }
    }

    public static function updateWalletAfterCancel($order): void
    {
        if (strtolower($order->type) == 'buy') {
            $history = new AvailableWalletHistory();
            $history->user_id = $order->client_id;
            $history->model_id = $order->id;
            $history->category = 'equity';
            $history->amount = $order->payout;
            $history->description = 'Increase available balance after order cancellation';
            $history->save();

            TransactionsController::updateWallet($order->client_id);

        }
    }

    public static function updateWalletAfterClose($order): void
    {
        if (strtolower($order->type) == 'buy') {
            $history = new AvailableWalletHistory();
            $history->user_id = $order->client_id;
            $history->model_id = $order->id;
            $history->category = 'equity';
            $history->amount = $order->payout;
            $history->description = 'Release available balance after order close';
            $history->save();

            TransactionsController::updateWallet($order->client_id);

        }
    }
}
