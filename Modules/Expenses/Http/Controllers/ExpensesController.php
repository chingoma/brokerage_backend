<?php

namespace Modules\Expenses\Http\Controllers;

use App\Exports\Expenses\ExpensesAllMonthsExport;
use App\Exports\Expenses\ExpensesCustomExport;
use App\Exports\Expenses\ExpensesMonthlyExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\AccountClass;
use App\Models\Accounting\AccountPlain;
use App\Models\Accounting\RealAccount;
use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Modules\CRM\Entities\CustomerPlain;
use Modules\Orders\Entities\Order;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ExpensesController extends Controller
{
    #[NoReturn]
    public function export_range(Request $request): BinaryFileResponse
    {
        $from = date('Y-m-d', strtotime($request->from));
        $end = date('Y-m-d', strtotime($request->end));

        return (new ExpensesCustomExport($from, $end))->download('expenses-'.$from.'_to_'.$end.'.xlsx');
    }

    #[NoReturn]
    public function export_monthly(Request $request): BinaryFileResponse
    {
        $start = new Carbon('first day of '.ucwords($request->month).' '.date('Y'));
        $end = new Carbon('last day of '.ucwords($request->month).' '.date('Y'));

        return (new ExpensesMonthlyExport($request->month))->from($start->toDateString())->end($end->toDateString())->download($request->month.'-expenses-report.xlsx');
    }

    #[NoReturn]
    public function export_all_months(Request $request): BinaryFileResponse
    {
        return (new ExpensesAllMonthsExport)->download('all-months-expenses-report.xlsx');
    }

    public function expenses_report(Request $request): JsonResponse
    {
        // January
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::expensesReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['december'] = round($revenueCredit, 2);

        return response()->json($revenue);

    }

    public function rejectSelected(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if (! empty($request->items)) {
                foreach ($request->items as $item) {
                    $transaction = Transaction::find($item);
                    Transaction::where('reference', $transaction->reference)->update(['status' => 'Rejected']);
                    DealingSheet::where('slip_no', $transaction->reference)->update(['closed' => 'no']);
                    Order::where('id', $transaction->order_id)->update(['closed' => 'no']);
                }
            }

            DB::commit();

            return $this->expenses($request);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function approveSelected(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if (! empty($request->items)) {
                foreach ($request->items as $item) {
                    $transaction = Transaction::find($item);
                    Transaction::where('reference', $transaction->reference)->update(['status' => 'Approved']);
                    DealingSheet::where('slip_no', $transaction->reference)->update(['closed' => 'yes']);
                    Order::where('id', $transaction->order_id)->update(['closed' => 'yes']);
                }
            }

            DB::commit();

            return $this->expenses($request);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function disApproveSelected(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if (! empty($request->items)) {
                foreach ($request->items as $item) {
                    $transaction = Transaction::find($item);
                    Transaction::where('reference', $transaction->reference)->update(['status' => 'Pending']);
                    DealingSheet::where('slip_no', $transaction->reference)->update(['closed' => 'no']);
                    Order::where('id', $transaction->order_id)->update(['closed' => 'no']);
                }
            }

            DB::commit();

            return $this->expenses($request);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function filter(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');

            $query = Transaction::expensesReport()->latest('transaction_date')->groupBy('reference')->latest('transaction_date')->groupBy('reference')->orderBy('id', 'desc');

            if (! empty($request->client)) {
                $query = $query->where('client_id', $request->client);
            }

            if (! empty($request->from) && ! empty($request->end)) {
                $query = $query->whereDate('transaction_date', '>=', date('Y-m-d', strtotime($request->from)))
                    ->whereDate('transaction_date', '<=', date('Y-m-d', strtotime($request->end)));
            }

            $expenses = $query->paginate($per_page);

            return response()->json($expenses);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_expense_cheque(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $reference = $this->generateReference('');
            $category = AccountCategory::find($request->category);

            // withdraw account
            $account = Account::find($category->credit_account);
            $class = AccountClass::find($account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionExpense(model: $receipt, amount: $request->amount, action: 'credit');
            $receipt->title = $request->description;
            $receipt->cheque_number = $request->cheque_number;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->status = 'Pending';
            $receipt->expense_type = 'single';
            $receipt->real_account_id = $request->real_account;
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = $category->type;
            $receipt->account_id = $account->id;
            $receipt->class_id = $class->id;
            $receipt->withdraw_account = 'yes';
            $receipt->payment_method_id = $request->payment_method;
            $receipt->account_category_id = $category->id;
            $receipt->save();

            Helper::transactionUID($receipt);
            // deposit account
            $account = Account::find($category->debit_account);
            $class = AccountClass::find($account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionExpense(model: $receipt, amount: $request->amount, action: 'debit');
            $receipt->title = $request->description;
            $receipt->cheque_number = $request->cheque_number;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->real_account_id = $request->real_account;
            $receipt->status = 'Pending';
            $receipt->expense_type = 'single';
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = $category->type;
            $receipt->account_id = $account->id;
            $receipt->class_id = $class->id;
            $receipt->payment_method_id = $request->payment_method;
            $receipt->account_category_id = $category->id;
            $receipt->save();
            Helper::transactionUID($receipt);
            DB::commit();

            return $this->expenses($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function expense(Request $request): JsonResponse
    {
        try {
            $receipt = Transaction::where('category', 'Expense')->where('reference', $request->reference)->first();
            $response['items'] = Transaction::where('reference', $request->reference)->orderBy('id', 'desc')->get();
            $response['title'] = $receipt->title;
            $response['payee'] = $receipt->client;
            $response['transaction_date'] = $receipt->transaction_date;
            $response['reference'] = $request->reference;

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function expenses(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $receipts = Transaction::expenses()->latest('transaction_date')->paginate($per_page);

            return response()->json($receipts);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function stats(): JsonResponse
    {

        // revenueLastMonth
        $start = new Carbon('first day of last month');
        $start = $start->startOfMonth();
        $end = new Carbon('last day of last month');
        $end = $end->endOfMonth();
        $revenueLastMonth = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $response['revenueLastMonth'] = round($revenueLastMonth, 2);

        // revenueCurrentMonth
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $revenueCurrentMonth = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $response['revenueCurrentMonth'] = round($revenueCurrentMonth, 2);

        // firstQuarter
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $firstQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $quarters['firstQuarter'] = round($firstQuarter, 2);

        // secondQuarter
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $secondQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $quarters['secondQuarter'] = round($secondQuarter, 2);

        // thirdQuarter
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $thirdQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $quarters['thirdQuarter'] = round($thirdQuarter, 2);

        // fourthQuarter
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $fourthQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $quarters['fourthQuarter'] = round($fourthQuarter, 2);

        // totalQuarter
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $totalQuarter = Transaction::whereDate('transaction_date', '<=', $end->toDateString())->where('status', 'approved')->whereDate('transaction_date', '>=', $start->toDateString())->whereIn('class_id', [11, 12])->sum('debit');
        $response['totalQuarter'] = number_format(round($totalQuarter, 2));

        $response['quarters'] = [$quarters['firstQuarter'], $quarters['secondQuarter'], $quarters['thirdQuarter'], $quarters['fourthQuarter']];

        return response()->json($response);
    }

    public function settings_data(): JsonResponse
    {
        try {
            $response['real_accounts'] = RealAccount::get();
            $response['categories'] = AccountCategory::where('type', 'expense')->get();
            $response['accounts'] = AccountPlain::get();
            $response['customers'] = CustomerPlain::get();
            $response['receiptMethods'] = PaymentMethod::get();

            return response()->json($response);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
