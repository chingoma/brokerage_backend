<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\Receipts\ReceiptsAllMonthsExport;
use App\Exports\Receipts\ReceiptsCustomExport;
use App\Exports\Receipts\ReceiptsMonthlyExport;
use App\Helpers\Helper;
use App\Helpers\MoneyHelper;
use App\Http\Controllers\Controller;
use App\Jobs\Statements\UpdateCustomerStatements;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\AccountClass;
use App\Models\Accounting\AccountPlain;
use App\Models\Accounting\RealAccount;
use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Modules\CRM\Entities\CustomerPlain;
use Modules\Orders\Entities\Order;
use Modules\Receipts\DTOs\CreateReceiptTransactionDTO;
use Modules\Receipts\Pipes\CreateReceiptTransactionPipe;
use Modules\Receipts\Pipes\CreateSimpleTransactionReceiptPipe;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ReceiptsController extends Controller
{
    #[NoReturn]
    public function export_range(Request $request): BinaryFileResponse
    {
        $from = date('Y-m-d', strtotime($request->from));
        $end = date('Y-m-d', strtotime($request->end));

        return (new ReceiptsCustomExport($from, $end))->download('receipts-'.$from.'_to_'.$end.'.xlsx');
    }

    #[NoReturn]
    public function export_monthly(Request $request): BinaryFileResponse
    {
        $start = new Carbon('first day of '.ucwords($request->month).' '.date('Y'));
        $end = new Carbon('last day of '.ucwords($request->month).' '.date('Y'));

        return (new ReceiptsMonthlyExport($request->month))->from($start->toDateString())->end($end->toDateString())->download($request->month.'-receipts-report.xlsx');
    }

    #[NoReturn]
    public function export_all_months(Request $request): BinaryFileResponse
    {
        return (new ReceiptsAllMonthsExport)->download('all-months-receipts-report.xlsx');
    }

    public function receipts_report(Request $request): JsonResponse
    {
        // January
        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['january'] = round($revenueCredit, 2);

        // February
        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['february'] = round($revenueCredit, 2);

        // March
        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['march'] = round($revenueCredit, 2);

        // April
        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['april'] = round($revenueCredit, 2);

        // May
        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['may'] = round($revenueCredit, 2);

        // June
        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['june'] = round($revenueCredit, 2);

        // July
        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['july'] = round($revenueCredit, 2);

        // August
        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['august'] = round($revenueCredit, 2);

        // September
        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['september'] = round($revenueCredit, 2);

        // October
        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['october'] = round($revenueCredit, 2);

        // November
        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
        $revenue['november'] = round($revenueCredit, 2);

        // December
        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $revenueCredit = Transaction::receiptsReport()->whereDate('transaction_date', '<=', $end->toDateString())->whereDate('transaction_date', '>=', $start->toDateString())->sum('debit');
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
                    //                    DealingSheet::where('slip_no', $transaction->reference)->update(['closed' => "no"]);
                    //                    Order::where('id', $transaction->order_id)->update(['closed' => "no"]);
                    UpdateCustomerStatements::dispatchAfterResponse($transaction->client_id);

                }
            }

            DB::commit();

            return $this->receipts($request);
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
                    //                    DealingSheet::where('slip_no', $transaction->reference)->update(['closed' => "yes"]);
                    //                    Order::where('id', $transaction->order_id)->update(['closed' => "yes"]);
                    UpdateCustomerStatements::dispatchAfterResponse($transaction->client_id);

                }
            }

            DB::commit();

            return $this->receipts($request);
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

            return $this->receipts($request);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function create_customer_deposit(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $reference = $this->generateReference();

            //accounts
            $cash_account = Account::find($request->cash_account);
            $deposit_account = Account::find($request->deposit_account);

            if ($cash_account->increase == $deposit_account->increase) {
                throw new Exception('Seems double entry is not set properly, Please double you Cash and Deposit Accounts');
            }

            // withdraw account
            $class = AccountClass::find($cash_account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionReceipt(model: $receipt, amount: $request->amount, action: $cash_account->increase);
            $receipt->title = $request->description;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->receipt_type = 'deposit';
            $receipt->status = 'Pending';
            $receipt->cash_account = 'yes';
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = 'Receipt';
            $receipt->account_id = $cash_account->id;
            $receipt->class_id = $class->id;
            $receipt->customer_action = 'Deposit';
            $receipt->payment_method_id = $request->payment_method;
            $receipt->save();

            // deposit account
            $class = AccountClass::find($deposit_account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionReceipt(model: $receipt, amount: $request->amount, action: $deposit_account->increase);
            $receipt->title = $request->description;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->status = 'Pending';
            $receipt->receipt_type = 'deposit';
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = 'Receipt';
            $receipt->account_id = $deposit_account->id;
            $receipt->class_id = $class->id;
            $receipt->customer_action = 'Deposit';
            $receipt->payment_method_id = $request->payment_method;
            $receipt->save();

            DB::commit();

            return $this->receipts($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_customer_deposit(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $reference = $request->reference;
            Transaction::where('category', 'Receipt')->where('reference', $reference)->delete();

            //accounts
            $cash_account = Account::find($request->cash_account);
            $deposit_account = Account::find($request->deposit_account);

            if ($cash_account->increase == $deposit_account->increase) {
                throw new Exception('Seems double entry is not set properly, Please double you Cash and Deposit Accounts');
            }

            // withdraw account
            $class = AccountClass::find($cash_account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionReceipt(model: $receipt, amount: $request->amount, action: $cash_account->increase);
            $receipt->title = $request->description;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->status = 'Pending';
            $receipt->receipt_type = 'deposit';
            $receipt->cash_account = 'yes';
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = 'Receipt';
            $receipt->account_id = $cash_account->id;
            $receipt->class_id = $class->id;
            $receipt->customer_action = 'Deposit';
            $receipt->payment_method_id = $request->payment_method;
            $receipt->save();

            // deposit account
            $class = AccountClass::find($deposit_account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionReceipt(model: $receipt, amount: $request->amount, action: $deposit_account->increase);
            $receipt->title = $request->description;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->status = 'Pending';
            $receipt->receipt_type = 'deposit';
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = 'Receipt';
            $receipt->account_id = $deposit_account->id;
            $receipt->class_id = $class->id;
            $receipt->customer_action = 'Deposit';
            $receipt->payment_method_id = $request->payment_method;
            $receipt->save();

            DB::commit();

            return $this->receipts($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_receipt_cheque(Request $request): JsonResponse
    {

        try {
            //            $id = $request->header("id");
            //            DB::beginTransaction();
            //            $timestamp = Helper::getTimestamp();
            //            $reference = $this->generateReference("");
            //            $category = AccountCategory::findOrFail($request->category);
            //            # cash account
            //            $account = Account::findOrFail($category->debit_account);
            //            $class = AccountClass::findOrFail($account->class_id);
            //
            //            $receipt = new Transaction();
            //            $receipt->external_reference = $request->reference;
            //            $this->setBusinessYear($receipt);
            //            $this->setTransactionReceipt(model: $receipt, amount: $request->amount, action: $account->increase);
            //            $receipt->title = $request->description;
            //            $receipt->cheque_number = $request->cheque_number;
            //            $receipt->transaction_date = $timestamp;
            //            $receipt->reference = $reference;
            //            $receipt->status = "Pending";
            //            $receipt->receipt_type = "single";
            //            $receipt->description = $request->description;
            //            $receipt->client_id = $request->payee;
            //            $receipt->category = $category->type;
            //            $receipt->account_category_id = $category->id;
            //            $receipt->account_id = $account->id;
            //            $receipt->class_id = $class->id;
            //            $receipt->cash_account = "yes";
            //            $receipt->payment_method_id = $request->payment_method;
            //            $receipt->real_account_id = $request->real_account;
            //
            //            $request->created_by = $id;
            //            $request->updated_by = $id;
            //
            //            $receipt->save();
            //            Helper::transactionUID($receipt);
            //
            //            # income account
            //            $account = Account::find($category->credit_account);
            //            $class = AccountClass::find($account->class_id);
            //            $receipt = new Transaction();
            //            $receipt->external_reference = $request->reference;
            //            $this->setBusinessYear($receipt);
            //            $this->setTransactionReceipt(model: $receipt, amount: $request->amount,action: $account->increase);
            //            $receipt->title = $request->description;
            //            $receipt->cheque_number = $request->cheque_number;
            //            $receipt->transaction_date = $timestamp;
            //            $receipt->reference = $reference;
            //            $receipt->status = "Pending";
            //            $receipt->receipt_type = "single";
            //            $receipt->description = $request->description;
            //            $receipt->client_id = $request->payee;
            //            $receipt->category = $category->type;
            //            $receipt->account_category_id = $category->id;
            //            $receipt->account_id = $account->id;
            //            $receipt->class_id = $class->id;
            //            $receipt->payment_method_id = $request->payment_method;
            //            $receipt->real_account_id = $request->real_account;
            //
            //            $request->created_by = $id;
            //            $request->updated_by = $id;
            //
            //            $receipt->save();
            //            Helper::transactionUID($receipt);
            //
            //            DB::commit();
            $requestData = CreateReceiptTransactionDTO::fromRequest($request);
            $pipes = [
                CreateReceiptTransactionPipe::class,
                CreateSimpleTransactionReceiptPipe::class,
            ];
            DB::beginTransaction();
            app(Pipeline::class)
                ->send($requestData)
                ->through($pipes)
                ->then(function ($simpleTransaction) {
                    return $simpleTransaction;
                });
            DB::commit();

            return $this->receipts($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_receipt_cheque(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $category = AccountCategory::find($request->category);
            $reference = $this->generateReference($request->reference);
            Transaction::where('category', $category->type)->where('reference', $reference)->delete();

            // cash account
            $account = Account::find($category->debit_account);
            $class = AccountClass::find($account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionReceipt(model: $receipt, amount: $request->amount, action: $account->increase);
            $receipt->title = $request->description;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->cheque_number = $request->cheque_number;
            $receipt->status = 'Pending';
            $receipt->receipt_type = 'single';
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = $category->type;
            $receipt->account_category_id = $category->id;
            $receipt->account_id = $account->id;
            $receipt->class_id = $class->id;
            $receipt->cash_account = 'yes';
            $receipt->payment_method_id = $request->payment_method;
            $receipt->real_account_id = $request->real_account;
            $receipt->save();

            // income account
            $account = Account::find($category->credit_account);
            $class = AccountClass::find($account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionReceipt(model: $receipt, amount: $request->amount, action: $account->increase);
            $receipt->title = $request->description;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->cheque_number = $request->cheque_number;
            $receipt->status = 'Pending';
            $receipt->receipt_type = 'single';
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = $category->type;
            $receipt->account_category_id = $category->id;
            $receipt->account_id = $account->id;
            $receipt->class_id = $class->id;
            $receipt->payment_method_id = $request->payment_method;
            $receipt->real_account_id = $request->real_account;
            $receipt->save();

            DB::commit();

            return $this->receipts($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $cash_account = Account::find($request->debit_account);

            if (! empty($request->items) && is_array(json_decode($request->items))) {

                foreach (json_decode($request->items) as $item) {

                    $category = AccountCategory::find($item->category);
                    $reference = $this->generateReference('');

                    $account = Account::find($category->debit_account);
                    $class = AccountClass::find($account->class_id);

                    $receipt = new Transaction();
                    $receipt->cash_account = 'yes';
                    $receipt->external_reference = $item->reference;
                    $this->setBusinessYear($receipt);
                    $this->setTransactionAction(model: $receipt, action: 'debit', amount: $item->amount);
                    $receipt->title = $item->description;
                    $receipt->cheque_number = '';
                    $receipt->transaction_date = $request->transaction_date;
                    $receipt->reference = $reference;
                    $receipt->status = 'Pending';
                    $receipt->receipt_type = 'multiple';
                    $receipt->action = 'debit';
                    $receipt->description = $item->description;
                    $receipt->client_id = $item->payee;
                    $receipt->category = $category->type;
                    $receipt->account_category_id = $category->id;
                    $receipt->account_id = $account->id;
                    $receipt->class_id = $class->id;
                    $receipt->payment_method_id = $request->payment_method;
                    $receipt->save();
                    Helper::transactionUID($receipt);

                    $account = Account::find($category->credit_account);
                    $class = AccountClass::find($account->class_id);

                    $receipt = new Transaction();
                    $receipt->external_reference = $item->reference;
                    $this->setBusinessYear($receipt);
                    $this->setTransactionAction(model: $receipt, action: 'credit', amount: $item->amount);
                    $receipt->title = $item->description;
                    $receipt->cheque_number = '';
                    $receipt->transaction_date = $request->transaction_date;
                    $receipt->reference = $reference;
                    $receipt->status = 'Pending';
                    $receipt->receipt_type = 'multiple';
                    $receipt->action = 'credit';
                    $receipt->description = $item->description;
                    $receipt->client_id = $item->payee;
                    $receipt->category = $category->type;
                    $receipt->account_category_id = $category->id;
                    $receipt->account_id = $account->id;
                    $receipt->class_id = $class->id;
                    $receipt->payment_method_id = $request->payment_method;
                    $receipt->save();
                    Helper::transactionUID($receipt);

                }
            }

            DB::commit();

            return $this->receipts($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function edit(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();

            $reference = $request->reference;
            Transaction::where('category', 'Receipt')->where('reference', $reference)->delete();

            $cash_account = Account::findOrFail($request->cash_account);
            $reference = $this->generateReference('');
            $amount = 0;
            if (! empty($request->items) && is_array(json_decode($request->items))) {
                foreach (json_decode($request->items) as $item) {
                    $account = Account::find($item->revenue_account);

                    if ($cash_account->increase == $account->increase) {
                        throw new Exception('Seems double entry is not set properly, Please double you Cash and Receipt Accounts');
                    }

                    $class = AccountClass::find($account->class_id);
                    $receipt = new Transaction();
                    $this->setBusinessYear($receipt);
                    $this->setTransactionAction(model: $receipt, action: $account->increase, amount: $item->amount);
                    $receipt->title = $request->title;
                    $receipt->cheque_number = $item->cheque_number;
                    $receipt->transaction_date = $request->transaction_date;
                    $receipt->reference = $reference;
                    $receipt->status = 'Pending';
                    $receipt->receipt_type = 'multiple';
                    $receipt->action = $account->increase;
                    $receipt->description = $item->description;
                    $receipt->client_id = $item->payee;
                    $receipt->category = 'Receipt';
                    $receipt->account_id = $item->revenue_account;
                    $receipt->class_id = $class->id;
                    $receipt->save();
                    $amount = $amount + MoneyHelper::sanitize($item->amount);
                }
            }

            // withdraw account
            $class = AccountClass::find($cash_account->class_id);
            $receipt = new Transaction();
            $this->setBusinessYear($receipt);
            $this->setTransactionReceipt(model: $receipt, amount: $amount, action: $cash_account->increase);
            $receipt->title = $request->description;
            $receipt->transaction_date = $request->transaction_date;
            $receipt->reference = $reference;
            $receipt->status = 'Pending';
            $receipt->receipt_type = 'multiple';
            $receipt->description = $request->description;
            $receipt->client_id = $request->payee;
            $receipt->category = 'Receipt';
            $receipt->account_id = $cash_account->id;
            $receipt->class_id = $class->id;
            $receipt->action = $cash_account->increase;
            $receipt->cash_account = 'yes';
            $receipt->save();

            DB::commit();

            return $this->receipts($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function receipt(Request $request): JsonResponse
    {
        try {
            $receipt = Transaction::where('category', 'Receipt')->where('reference', $request->reference)->first();
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

    public function receiptSingle(Request $request): JsonResponse
    {
        try {
            $receipt = Transaction::where('receipt_type', 'single')->where('category', 'Receipt')->where('reference', $request->reference)->first();
            $response['cash_account'] = Transaction::where('cash_account', 'yes')->where('receipt_type', 'single')->where('category', 'Receipt')->where('reference', $request->reference)->first()->account_id;
            $response['revenue_account'] = Transaction::where('cash_account', 'no')->where('receipt_type', 'single')->where('category', 'Receipt')->where('reference', $request->reference)->first()->account_id;
            $response['description'] = $receipt->description;
            $response['amount'] = $receipt->amount;
            $response['payee'] = $receipt->client_id;
            $response['receipt_type'] = $receipt->receipt_type;
            $response['cheque_number'] = $receipt->amount;
            $response['transaction_date'] = $receipt->transaction_date;
            $response['reference'] = $request->reference;

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function receiptMultiple(Request $request): JsonResponse
    {
        try {
            $receipt = Transaction::where('cash_account', 'yes')->where('category', 'Receipt')->where('reference', $request->reference)->firstOrFail();
            $response['items'] = Transaction::where('cash_account', 'no')->where('reference', $request->reference)->orderBy('id', 'desc')->get();
            $response['cash_account'] = $receipt->account_id;
            $response['receipt_type'] = $receipt->receipt_type;
            $response['description'] = $receipt->description;
            $response['transaction_date'] = $receipt->transaction_date;
            $response['reference'] = $request->reference;

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            $receipt = Account::find($request->id);
            $receipt->delete();

            return $this->receipts($request);

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function receipts(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $receipts = Transaction::receipts()->latest('transaction_date')->paginate($per_page);

            return response()->json($receipts);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function filter(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');

            $query = Transaction::receipts()->latest('transaction_date');

            if (! empty($request->client)) {
                $query = $query->where('client_id', $request->client);
            }

            //            if(!empty($request->from) && !empty($request->end)){
            //                $query = $query->whereDate("transaction_date",">=",date("Y-m-d", strtotime($request->from)))
            //                        ->whereDate("transaction_date","<=",date("Y-m-d", strtotime($request->end)));
            //            }

            $receipts = $query->paginate($per_page);

            return response()->json($receipts);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settings_data(): JsonResponse
    {
        try {
            $response['real_accounts'] = RealAccount::get();
            $response['categories'] = AccountCategory::where('type', 'receipt')->get();
            $response['accounts'] = AccountPlain::get();
            $response['customers'] = CustomerPlain::get();
            $response['receiptMethods'] = PaymentMethod::get();

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
