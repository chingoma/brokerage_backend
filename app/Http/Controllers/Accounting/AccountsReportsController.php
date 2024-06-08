<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\Income\IncomeAllMonthsExport;
use App\Exports\Income\IncomeCustomExport;
use App\Exports\Income\IncomeMonthlyExport;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AccountsReportsController extends Controller
{
    #[NoReturn]
    public function export_range(Request $request): BinaryFileResponse
    {
        $from = date('Y-m-d', strtotime($request->from));
        $end = date('Y-m-d', strtotime($request->end));

        return (new IncomeCustomExport($from, $end))->download('income-'.$from.'_to_'.$end.'.xlsx');
    }

    #[NoReturn]
    public function downloadPdf(Request $request)
    {
        $start = new Carbon('first day of '.ucwords($request->month).' '.date('Y'));
        $end = new Carbon('last day of '.ucwords($request->month).' '.date('Y'));

        return (new IncomeMonthlyExport($request->month))->from($start->toDateString())->end($end->toDateString())->download($request->month.'-income-report.xlsx');
    }

    #[NoReturn]
    public function export_all_months(Request $request)
    {
        return (new IncomeAllMonthsExport)->download('all-months-income-report.xlsx');
    }

    public function incomeStats(): JsonResponse
    {

        // revenueLastMonth
        $start = new Carbon('first day of last month');
        $start = $start->startOfMonth();
        $end = new Carbon('last day of last month');
        $end = $end->endOfMonth();
        $revenueLastMonth = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $response['revenueLastMonth'] = round($revenueLastMonth, 2);

        // revenueCurrentMonth
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $revenueCurrentMonth = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $response['revenueCurrentMonth'] = round($revenueCurrentMonth, 2);

        // firstQuarter
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $firstQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $quarters['firstQuarter'] = round($firstQuarter, 2);

        // secondQuarter
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $secondQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $quarters['secondQuarter'] = round($secondQuarter, 2);

        // thirdQuarter
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $thirdQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $quarters['thirdQuarter'] = round($thirdQuarter, 2);

        // fourthQuarter
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $fourthQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $quarters['fourthQuarter'] = round($fourthQuarter, 2);

        // totalQuarter
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $totalQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [10])->sum('credit');
        $response['totalQuarter'] = number_format(round($totalQuarter, 2));

        $response['quarters'] = [$quarters['firstQuarter'], $quarters['secondQuarter'], $quarters['thirdQuarter'], $quarters['fourthQuarter']];

        return response()->json($response);
    }

    public function incomes(): JsonResponse
    {

        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())
            ->whereDate('transaction_date', '>=', $start->toDateString())

            ->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenueDebit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('debit');
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [10])->sum('credit');
        $revenue['december'] = round($revenueCredit, 2);

        return response()->json($revenue);
    }

    public function expenses(): JsonResponse
    {
        // January
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('credit');
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->where('status', 'approved')->whereIn('class_id', [11, 12])->sum('debit');
        $revenue['december'] = round($revenueCredit, 2);

        return response()->json($revenue);
    }
}
