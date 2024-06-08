<?php

// Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Models\Accounting\AccountSetting;
use App\Models\Accounting\Transaction;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\SimpleTransaction;
use Modules\Bonds\Entities\BondExecution;
use Modules\Bonds\Entities\BondExecutionId;
use Modules\Bonds\Entities\BondId;
use Modules\Bonds\Entities\BondOrder;
use Modules\Bonds\Entities\BondPrimaryExecutionId;
use Modules\Bonds\Entities\BondPrimaryOrderId;
use Modules\Transactions\Http\Controllers\TransactionsController;
use Modules\Wallet\Entities\AvailableWalletHistory;
use Modules\Wallet\Entities\BondsOnHold;

class BondsHelper
{
    public static function clearFees($BondExecution): void
    {
        Transaction::where('reference', $BondExecution->slip_no)->delete();
        $BondExecution->commission_step_one = 0;
        $BondExecution->commission_step_two = 0;
        $BondExecution->dse = 0;
        $BondExecution->cmsa = 0;
        $BondExecution->fidelity = 0;
        $BondExecution->cds = 0;
        $BondExecution->total_fees = 0;
        $BondExecution->vat = 0;
        $BondExecution->brokerage = 0;
        $BondExecution->total_commissions = 0;
        $BondExecution->payout = 0;
    }

    public static function setCommissions($BondExecution): void
    {
        $commissions = BondsHelper::brokerageCommissions($BondExecution);
        $fees = $commissions['fees'];
        $BondExecution->commission_step_one = $commissions['step_one'];
        $BondExecution->commission_step_two = $commissions['step_two'];
        $BondExecution->dse = $fees['dse'];
        $BondExecution->cmsa = $fees['cmsa'];
        $BondExecution->fidelity = 0;
        $BondExecution->cds = $fees['cds'];
        $BondExecution->total_fees = $commissions['total'];
        $BondExecution->vat = 0.18 * $commissions['brokerage'];
        $BondExecution->brokerage = $commissions['brokerage'];
        $BondExecution->total_commissions = $commissions['total_fees'];

        if (strtolower($BondExecution->type) == 'buy') {
            $BondExecution->payout = $BondExecution->amount + $commissions['total'] + $BondExecution->other_charges;
        } else {
            $BondExecution->payout = $BondExecution->amount - $commissions['total'] - $BondExecution->other_charges;
        }
    }

    public static function brokerageCommissions($dealingSheet): array
    {
        $user = \DB::table('users')->find($dealingSheet->client_id);
        $category = \DB::table('customer_categories')->find($user->category_id);
        $scheme = \DB::table('bond_schemes')->find($category->bond_scheme);
        $amount = $dealingSheet->face_value;
        $balance = $amount;
        $totalCommissionRates = self::brokerCommissionsTotal($scheme, $dealingSheet);
        $stepOneCommission = 0;
        $stepTwoCommission = 0;

        if (strtolower($scheme->mode) == 'default') {

            if ($balance >= 40000000) {
                $stepOneCommission = self::commissionStep1($scheme, 40000000);
            } else {
                $stepOneCommission = self::commissionStep1($scheme, $amount);
            }

            if ($amount > 40000000) {
                // Step 2
                $step2Amount = $amount - 40000000;
                $stepTwoCommission = self::commissionStep2($scheme, $step2Amount);
            }

            $totalCommissions = $stepOneCommission + $stepTwoCommission;
        } else {
            $totalCommissions = round($amount * floatval($scheme->flat_rate), 4);
        }

        $vat = $totalCommissions * 0.18;
        $grandCommissions = $vat + $totalCommissions + $totalCommissionRates;

        $result['brokerage'] = $totalCommissions;
        $result['fees'] = self::brokerCommissions($scheme, $dealingSheet);
        $result['total_fees'] = $totalCommissionRates;
        $result['vat'] = $vat;
        $result['total'] = $grandCommissions;
        $result['step_one'] = $stepOneCommission;
        $result['step_two'] = $stepTwoCommission;

        return $result;
    }

    public static function brokerRates($scheme, $order): array
    {
        $data = [];
        if ($order->market == 'primary') {
            $data['cmsa'] = 0;
            $data['dse'] = 0;
            $data['cds'] = 0;
        } else {
            $data['cmsa'] = $scheme->cmsa_fee;
            $data['dse'] = $scheme->dse_fee;
            $data['cds'] = $scheme->csdr_fee;
        }

        return $data;
    }

    public static function commissionStep1($scheme, $amount): float
    {
        return round($amount * $scheme->step_one, 4);
    }

    public static function commissionStep2($scheme, $amount): float
    {
        return round($amount * $scheme->step_two, 4);
    }

    public static function brokerCommissions($scheme, $bondExecution): array
    {
        $rates = self::brokerRates($scheme, $bondExecution);
        $data['cmsa'] = round($rates['cmsa'] * $bondExecution->amount, 4);
        $data['dse'] = round($rates['dse'] * $bondExecution->face_value, 4);
        $data['cds'] = $rates['cds'] * $bondExecution->face_value;

        return $data;
    }

    public static function brokerCommissionsTotal($scheme, $bondExecution): float
    {
        $rates = self::brokerRates($scheme, $bondExecution);
        $data['cmsa'] = round($rates['cmsa'] * $bondExecution->amount, 4);
        $data['dse'] = round($rates['dse'] * $bondExecution->face_value, 4);
        $data['cds'] = $rates['cds'] * $bondExecution->face_value;

        return round($data['cmsa'], 4) + round($data['dse'], 4) + round($data['cds'], 4);
    }

    public static function BondOrderUID(BondOrder $model): string
    {

        if ($model->market == 'primary') {
            $cat = (strtolower($model->category) == 'bond') ? 'TBO' : 'TBI';
            $cat = (strtolower($model->category) == 'bond') ? 'BOT' : 'BOT';
        } else {
            $cat = 'CB';
        }

        $systemDate = Helper::systemDateTime($model->date);
        $year = date('Y', strtotime($systemDate['today']));

        $id = $cat.'/'.$year.'/';

        $status = BondId::withTrashed()
            ->where('year', $year)
            ->latest('lap')
            ->limit(1)
            ->first();

        if (empty($status)) {
            $nextLap = 1;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = BondId::withTrashed()
            ->where('year', $year)
            ->where('lap', $nextLap)
            ->latest()
            ->limit(1)
            ->first();

        if (empty($nextStatus)) {
            $id = $id.$nextLap;
            $data = new BondId();
            $data->year = $year;
            $data->uid = $id;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->save();
        } else {
            self::BondOrderUID($model);
        }

        return $id;
    }

    public static function BondExecutionUID(BondExecution $model): string
    {

        $tp = '';
        if ($model->market == 'primary') {
//            $cat = (strtolower($model->category) == 'bond') ? 'TBO' : 'TBI';
            $cat = (strtolower($model->category) == 'bond') ? 'BOT' : 'BOT';
        } else {
            $tp = (strtolower($model->type) == 'buy') ? 'P' : 'S';
            $cat = 'CB';
        }

        $systemDate = Helper::systemDateTime();
        $day = date('d', strtotime($systemDate['today']));
        $month = date('m', strtotime($systemDate['today']));
        $year = date('Y', strtotime($systemDate['today']));

        $id = $cat.'/'.$year.'/';
        $ref = $cat.$tp.$day.$month.$year.'/';

        $status = BondExecutionId::withTrashed()
            ->where('year', $year)
            ->latest('lap')
            ->limit(1)
            ->first();

        if (empty($status)) {
            $nextLap = 1;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = BondExecutionId::withTrashed()
            ->where('year', $year)
            ->where('lap', $nextLap)
            ->latest()
            ->limit(1)
            ->first();

        if (empty($nextStatus)) {
            $id = $id.$nextLap;
            $data = new BondExecutionId();
            $data->year = $year;
            $data->uid = $id;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->reference = $ref.$nextLap;
            $model->save();
        } else {
            self::BondExecutionUID($model);
        }

        return $id;
    }

    public static function BondPrimaryExecutionUID(BondExecution $model): string
    {

        $tp = '';
        $cat = (strtolower($model->category) == 'bond') ? 'BOT' : 'BOT';

        $inputDate = date("Y-m-d", strtotime($model->trade_date));
        $systemDate = Helper::systemDateTime($inputDate);
        $day = date('d', strtotime($systemDate['today']));
        $month = date('m', strtotime($systemDate['today']));
        $year = date('Y', strtotime($systemDate['today']));

        $id = $cat.'/'.$year.'/';
        $ref = $cat.$tp.$day.$month.$year.'/';

        $status = BondPrimaryExecutionId::withTrashed()
            ->where('year', $year)
            ->latest('lap')
            ->limit(1)
            ->first();

        if (empty($status)) {
            $nextLap = 1;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = BondPrimaryExecutionId::withTrashed()
            ->where('year', $year)
            ->where('lap', $nextLap)
            ->latest()
            ->limit(1)
            ->first();

        if (empty($nextStatus)) {
            $id = $id.$nextLap;
            $data = new BondPrimaryExecutionId();
            $data->year = $year;
            $data->uid = $id;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->reference = $ref.$nextLap;
            $model->save();
        } else {
            self::BondPrimaryExecutionUID($model);
        }

        return $id;
    }

    public static function BondPrimaryOrderUID(BondOrder $model): string
    {

        $cat = (strtolower($model->category) == 'bond') ? 'BOT' : 'BOT';

        $systemDate = Helper::systemDateTime();
        $year = date('Y', strtotime($systemDate['today']));

        $id = $cat.'/'.$year.'/';

        $status = BondPrimaryOrderId::withTrashed()
            ->where('year', $year)
            ->latest('lap')
            ->limit(1)
            ->first();

        if (empty($status)) {
            $nextLap = 1;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = BondPrimaryOrderId::withTrashed()
            ->where('year', $year)
            ->where('lap', $nextLap)
            ->latest()
            ->limit(1)
            ->first();

        if (empty($nextStatus)) {
            $id = $id.$nextLap;
            $data = new BondPrimaryOrderId();
            $data->year = $year;
            $data->uid = $id;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->save();
        } else {
            self::BondPrimaryOrderUID($model);
        }

        return $id;
    }

    public static function _process_order_buy($order): void
    {
        $orderData = BondOrder::findOrFail($order->order_id);
        $setting = AccountSetting::first();
        $category = ($orderData->has_custodian == 'yes') ? 'Custodian' : 'Bond';

        Transaction::where('reference', $order->slip_no)->delete();
        // reduce liability for customer because customer deposited cash for buying shares
        $transaction = new Transaction();
        $transaction->external_reference = $order->uid;
        $transaction->transaction_date = $order->trade_date;
        $transaction->title = 'Purchase of '.$order->bond->number;
        $transaction->amount = $order->amount + $order->total_fees;
        $transaction->status = 'Pending';
        $transaction->action = 'Debit';
        $transaction->category = $category;
        $transaction->customer_action = 'Withdraw';
        $transaction->debit = $order->payout;
        $transaction->credit = 0;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Purchase of '.$order->bond->number;
        if (strtolower($category) == 'custodian') {
            $transaction->custodian_id = $orderData->custodian_id;
            $transaction->account_id = $setting->custodian_account;
            $transaction->class_id = Helper::account($setting->custodian_account)->class_id;
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
        self::__bondBuySimpleTransaction($simpleTransaction, $order);

        // reduce cash because we have used cash to buy shares
        $transaction = new Transaction();
        $transaction->uid = $uid;
        $transaction->external_reference = $order->uid;
        $transaction->transaction_date = $order->trade_date;
        $transaction->title = 'Purchase of '.$order->bond->number;
        $transaction->amount = $order->payout;
        $transaction->status = 'Pending';
        $transaction->debit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->credit = $order->amount + $order->total_fees;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Purchase of '.$order->bond->number;
        if (strtolower($category) == 'custodian') {
            $transaction->custodian_id = $orderData->custodian_id;
            $transaction->account_id = $setting->custodian_account;
            $transaction->class_id = Helper::account($setting->custodian_account)->class_id;
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
        $orderData = BondOrder::find($order->order_id);
        $setting = AccountSetting::first();
        $category = ($orderData->has_custodian == 'yes') ? 'Custodian' : 'Bond';
        Transaction::where('reference', $order->slip_no)->delete();
        // reduce liability for customer because customer deposited cash for buying shares
        $transaction = new Transaction();
        $transaction->title = 'Sale of '.$order->bond->number;
        $transaction->transaction_date = $order->trade_date;
        $transaction->amount = $order->amount - $order->total_fees;
        $transaction->status = 'Pending';
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->customer_action = 'Deposit';
        $transaction->credit = $order->amount - $order->total_fees;
        $transaction->debit = 0;
        $transaction->reference = $order->slip_no;
        $transaction->description = 'Processing a Bond';
        if (strtolower($category) == 'custodian') {
            $transaction->custodian_id = $orderData->custodian_id;
            $transaction->account_id = $setting->custodian_account;
            $transaction->class_id = Helper::account($setting->custodian_account)->class_id;
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
        self::__bondSaleSimpleTransaction($simpleTransaction, $order);
        // reduce cash because we have used cash to buy shares
        $transaction = new Transaction();
        $transaction->uid = $uid;
        $transaction->transaction_date = $order->trade_date;
        $transaction->title = 'Sale of '.$order->bond->number;
        $transaction->amount = $order->amount - $order->total_fees;
        $transaction->status = 'Pending';
        $transaction->credit = 0;
        $transaction->action = 'Credit';
        $transaction->category = $category;
        $transaction->debit = $order->amount - $order->total_fees;
        $transaction->reference = $order->slip_no;
        if (strtolower($category) == 'custodian') {
            $transaction->custodian_id = $orderData->custodian_id;
            $transaction->account_id = $setting->custodian_account;
            $transaction->class_id = Helper::account($setting->custodian_account)->class_id;
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

    public static function _recordRevenueAndCommissions($order, $uid): void
    {
        $orderData = BondOrder::find($order->order_id);
        $setting = AccountSetting::first();

        $category = 'Bond';
        // record fees
        if ($order->other_charges > 0) {
            $transaction = new Transaction();
            $transaction->external_reference = $order->uid;
            $transaction->transaction_date = $order->trade_date;
            $transaction->client_id = $orderData->client_id;
            $transaction->amount = $order->other_charges;
            $transaction->status = 'Pending';
            $transaction->debit = 0;
            $transaction->action = 'Credit';
            $transaction->category = $category;
            $transaction->credit = $order->other_charges;
            $transaction->reference = $order->slip_no;
            $transaction->description = 'Processing Bond';
            $transaction->title = 'Processing Bond';
            $transaction->account_id = Helper::account($setting->order_liability_account)->id;
            $transaction->class_id = Helper::account($setting->order_liability_account)->class_id;
            $transaction->order_id = $orderData->id;
            $transaction->financial_year_id = Helper::business()->financial_year;
            $transaction->uid = $uid;
            $transaction->save();

            $transaction = new Transaction();
            $transaction->external_reference = $order->uid;
            $transaction->amount = $order->other_charges;
            $transaction->status = 'Pending';
            $transaction->credit = 0;
            $transaction->action = 'Debit';
            $transaction->category = $category;
            $transaction->debit = $order->other_charges;
            $transaction->reference = $order->slip_no;
            $transaction->description = 'Processing a Bond';
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
        $transaction->description = 'Processing a Bond';
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
        $transaction->description = 'Processing a Bond';
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
        $transaction->description = 'Processing a Bond';
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
        $transaction->description = 'Processing a Bond';
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
        $transaction->description = 'Processing a Bond';
        $transaction->title = 'Processing a Bond';
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
        $transaction->description = 'Processing a Bond';
        $transaction->title = 'Processing a Bond';
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
        $transaction->description = 'Processing a Bond';
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
        $transaction->description = 'Processing a Bond';
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

    private static function __bondBuySimpleTransaction(Transaction $transaction, $order): void
    {
        if (strtolower($transaction->category) == 'custodian') {
            $type = 'Custodian';
        } else {
            $type = 'Wallet';
        }

        $bond = DB::table('bonds')->find($order->bond_id);
        $statement = new SimpleTransaction();
        $statement->client_id = $order->client_id;
        $statement->trans_id = $transaction->id;
        $statement->trans_category = $transaction->category;
        $statement->trans_reference = $transaction->reference;
        $statement->order_type = 'bond';
        $statement->order_id = $order->id;
        $statement->date = $transaction->transaction_date;
        $statement->type = $type;
        $statement->category = 'PURCHASE';
        $statement->reference = $order->uid;
        $statement->particulars = ' Purchase of '.$bond->security_name.' '.$bond->category;
        $statement->quantity = $order->executed;
        $statement->price = $order->price;
        $statement->debit = $order->payout;
        $statement->credit = 0;
        $statement->action = 'debit';
        $statement->amount = $order->payout;
        $statement->status = 'pending';
        $statement->save();
    }

    private static function __bondSaleSimpleTransaction(Transaction $transaction, $order): void
    {
        if (strtolower($transaction->category) == 'custodian') {
            $type = 'Custodian';
        } else {
            $type = 'Wallet';
        }

        $bond = DB::table('bonds')->find($order->bond_id);
        $statement = new SimpleTransaction();
        $statement->client_id = $order->client_id;
        $statement->trans_id = $transaction->id;
        $statement->trans_category = $transaction->category;
        $statement->trans_reference = $transaction->reference;
        $statement->order_type = 'bond';
        $statement->order_id = $order->id;
        $statement->date = $transaction->transaction_date;
        $statement->type = $type;
        $statement->category = 'SALE';
        $statement->reference = $order->uid;
        $statement->particulars = ' Sale of '.$bond->security_name.' '.$bond->category;
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
        $order = BondOrder::find($id);
        $executed = DB::table('bond_executions')->where('status', '!=', 'cancelled')->where('order_id', $id)->sum('executed');
        $difference = $order->face_value - $executed;
        if ($difference <= 0) {
            $order->status = 'complete';
            $order->closed = 'yes';
            BondsOnHold::where('bond_id', $order->id)->delete();
        }
        $order->balance = $difference;
        $order->save();
    }

    public static function updateWallet($order): void
    {
        if (strtolower($order->type) == 'buy') {
            $onHold = new BondsOnHold();
            $onHold->amount = $order->payout;
            $onHold->user_id = $order->client_id;
            $onHold->bond_id = $order->id;
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
            $history->category = 'bond';
            $history->amount = $order->payout;
            $history->description = 'Decrease available balance';
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
            $history->category = 'bond';
            $history->amount = $order->payout;
            $history->description = 'Decrease available balance';
            $history->save();

            TransactionsController::updateWallet($order->client_id);

        }
    }
}
