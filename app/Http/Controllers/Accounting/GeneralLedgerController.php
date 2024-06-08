<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\Transaction;
use Illuminate\Http\Request;
use Throwable;

class GeneralLedgerController extends Controller
{
    public function ledger(Request $request)
    {
        try {
            $account = Account::find($request->id);

            return response()->json($account, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function ledgers()
    {
        try {
            $accounts = Transaction::orderBy('id', 'desc')->where('status', 'Approved')->paginate(env('perpage'));

            return response()->json($accounts, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
