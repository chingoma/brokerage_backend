<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountPlain;
use App\Models\Accounting\AccountSetting;
use App\Models\Accounting\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FidelityReportController extends Controller
{
    public function collected(Request $request): JsonResponse
    {

        $settings = AccountSetting::first();
        $account = AccountPlain::find($settings->fidelity_fee_account);
        $id = $settings->fidelity_fee_account;

        // January
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['december'] = round($revenueCredit, 2);

        return response()->json($revenue);

    }

    public function stats(Request $request): JsonResponse
    {

        $settings = AccountSetting::first();
        $account = AccountPlain::find($settings->fidelity_fee_account);
        $id = $settings->fidelity_fee_account;

        $total = Transaction::where('account_id', $id)->sum($account->increase);
        $paid = Transaction::where('account_id', $id)->sum($account->decrease);
        $data['balance'] = round($total - $paid, 2);
        $data['paid'] = round($paid, 2);
        $data['total'] = round($total, 2);

        return response()->json($data);

    }

    public function paid(Request $request): JsonResponse
    {

        $settings = AccountSetting::first();
        $account = AccountPlain::find($settings->fidelity_fee_account);
        $id = $settings->fidelity_fee_account;

        // January
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->increase);
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('account_id', $id)->sum($account->decrease);
        $revenue['december'] = round($revenueCredit, 2);

        return response()->json($revenue);

    }
}
