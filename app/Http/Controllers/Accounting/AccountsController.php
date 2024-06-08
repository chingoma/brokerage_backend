<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountClass;
use App\Models\Accounting\AccountPlain;
use App\Models\Accounting\BalanceSheet;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\TransactionAccounts;
use App\Models\Accounting\TrialBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AccountsController extends Controller
{
    public function create(Request $request)
    {
        try {
            $class = AccountClass::find($request->class);
            $account = new Account();
            $this->setBusiness($account);
            $account->code = $request->code;
            $account->description = $request->description;
            $account->name = $request->name;
            $account->nature = $class->increase;
            $account->increase = $class->increase;
            $account->decrease = $class->decrease;
            $account->class_id = $class->id;
            $account->save();

            return $this->accounts();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            $account = Account::find($request->id);
            $class = AccountClass::find($request->class);
            $this->setBusiness($account);
            $account->code = $request->code;
            $account->description = $request->description;
            $account->name = $request->name;
            $account->nature = $class->increase;
            $account->increase = $class->increase;
            $account->decrease = $class->decrease;
            $account->class_id = $class->id;
            $account->save();

            return $this->accounts();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function account(Request $request)
    {
        try {
            $account = Account::find($request->id);

            return response()->json($account, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try {
            $account = Account::find($request->id);
            $account->delete();

            return $this->accounts();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function accounts()
    {
        try {
            $accounts = Account::paginate(300);

            return response()->json($accounts, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function balanceSheet()
    {
        try {
            $ids = TransactionAccounts::where('status', 'approved')->get(['account_id'])->toArray();

            $assets = AccountPlain::whereIn('id', $ids)->whereIn('class_id', [1, 2, 3, 4, 5])->get(['id'])->toArray();
            $response['assets'] = BalanceSheet::whereIn('id', $assets)->get();

            $totalAsset = 0;
            if (! empty($response['assets'])) {
                foreach ($response['assets'] as $item) {
                    $totalAsset = $totalAsset + $item->balance;
                }
            }

            $liabilities = AccountPlain::whereIn('id', $ids)->whereIn('class_id', [6, 7, 8])->get(['id'])->toArray();
            $response['liabilities'] = BalanceSheet::whereIn('id', $liabilities)->get();
            $totalLiability = 0;
            if (! empty($response['liabilities'])) {
                foreach ($response['liabilities'] as $item) {
                    $totalLiability = $totalLiability + $item->balance;
                }
            }

            $equities = AccountPlain::whereIn('id', $ids)->whereIn('class_id', [9])->get(['id'])->toArray();
            $response['equities'] = BalanceSheet::whereIn('id', $equities)->get();
            $totalEquity = 0;
            if (! empty($response['equities'])) {
                foreach ($response['equities'] as $item) {
                    $totalEquity = $totalEquity + $item->balance;
                }
            }
            $response['totalAsset'] = $totalAsset;
            $response['totalLiability'] = $totalLiability;
            $response['totalEquity'] = $totalEquity;

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse(json_encode($ids));
        }
    }

    public function financialStatement()
    {
        try {

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

            $response['totalRevenue'] = $totalRevenue;
            $response['totalExpense'] = $totalExpense;
            $response['netProfit'] = $totalRevenue - $totalExpense;

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse(json_encode($ids));
        }
    }

    public function trialBalance()
    {
        try {
            $ids = TransactionAccounts::where('status', 'approved')->get(['account_id'])->toArray();
            $response['accounts'] = TrialBalance::whereIn('id', $ids)->get();
            $response['debit'] = Transaction::where('status', 'approved')->sum('debit');
            $response['credit'] = Transaction::where('status', 'approved')->sum('credit');

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse(json_encode($ids));
        }
    }

    public function settings_data(): JsonResponse
    {
        try {
            $settings['classes'] = AccountClass::all();

            return response()->json($settings);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
