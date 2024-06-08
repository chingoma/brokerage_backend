<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\TransactionAccounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class ChatsController extends Controller
{
    public function stockAnalysis(Request $request): JsonResponse
    {

        try {

            $ids = TransactionAccounts::where('status', 'approved')->get(['account_id'])->toArray();

            $response['income'] = $this->incomes($ids);
            $response['expense'] = $this->expenses($ids);
            $response['cashFlow'] = $this->cashFlow($ids);

            return response()->json($response);
        } catch (Throwable $throwable) {
            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function cashFlowData(Request $request): JsonResponse
    {
        try {
            $ids = TransactionAccounts::where('status', 'approved')->get(['account_id'])->toArray();

            return response()->json($this->cashFlow($ids));
        } catch (Throwable $throwable) {
            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function incomes($ids): array
    {
        // January
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenueDebit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('debit');
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $revenue['december'] = round($revenueCredit, 2);

        return $revenue;
    }

    public function expenses($ids): array
    {
        // January
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('credit');
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['december'] = round($revenueCredit, 2);

        return $revenue;
    }

    public function cashFlow($ids): array
    {
        // January
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('credit');
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::groupBy('reference')->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [1])->sum('debit');
        $revenue['december'] = round($revenueCredit, 2);

        return $revenue;
    }
}
