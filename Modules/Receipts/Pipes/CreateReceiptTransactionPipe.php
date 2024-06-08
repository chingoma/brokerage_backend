<?php

namespace Modules\Receipts\Pipes;

use App\Helpers\Helper;
use App\Helpers\MoneyHelper;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\AccountClass;
use App\Models\Accounting\Transaction;
use Closure;
use Exception;
use Modules\Accounting\Helpers\AccountingHelper;
use Modules\Receipts\DTOs\CreateReceiptTransactionDTO;

class CreateReceiptTransactionPipe
{
    /**
     * @throws Exception
     */
    public function handle(CreateReceiptTransactionDTO $createReceiptDTO, Closure $next)
    {

        try {
//            $date = date("Y-m-d",strtotime(request()->get("date")));
            $timestamp = Helper::systemDateTime();
            $reference = AccountingHelper::generateReference();
            $category = AccountCategory::findOrFail($createReceiptDTO->category);

            // debit account
            $account = Account::findOrFail($category->debit_account);
            $class = AccountClass::findOrFail($account->class_id);

            $receipt = new Transaction();
            $receipt->transaction_date = $timestamp['timely'];
            $receipt->reference = $reference;
            $receipt->category = $category->type;
            $receipt->account_category_id = $category->id;
            $receipt->account_id = $account->id;
            $receipt->class_id = $class->id;
            self::__setTransactionDebit(model: $receipt, amount: $createReceiptDTO->amount);
            self::__setTransaction($receipt, $createReceiptDTO);
            $receipt->save();
            Helper::transactionUID($receipt);
            $uid = $receipt->uid;
            // credit account
            $account = Account::findOrFail($category->credit_account);
            $class = AccountClass::findOrFail($account->class_id);

            $receipt = new Transaction();
            $receipt->uid = $uid;
            $receipt->transaction_date = $timestamp['timely'];
            $receipt->reference = $reference;
            $receipt->category = $category->type;
            $receipt->account_category_id = $category->id;
            $receipt->account_id = $account->id;
            $receipt->class_id = $class->id;
            self::__setTransactionCredit(model: $receipt, amount: $createReceiptDTO->amount);
            self::__setTransaction($receipt, $createReceiptDTO);
            $receipt->save();

            return $next($receipt);
        } catch (\Throwable $throwable) {
            report($throwable);
            throw new Exception($throwable->getMessage());
        }
    }

    private static function __setTransactionDebit($model, $amount): void
    {
        $model->debit = MoneyHelper::sanitize($amount);
        $model->credit = 0;
        $model->amount = MoneyHelper::sanitize($amount);
        $model->action = 'debit';

    }

    private static function __setTransactionCredit($model, $amount): void
    {
        $model->credit = MoneyHelper::sanitize($amount);
        $model->debit = 0;
        $model->amount = MoneyHelper::sanitize($amount);
        $model->action = 'credit';

    }

    private static function __setTransaction($receipt, $createReceiptDTO): void
    {

        $id = request()->header('id');
        $receipt->created_by = $id;
        $receipt->updated_by = $id;
        $receipt->cash_account = 'yes';
        AccountingHelper::setBusinessYear($receipt);
        $receipt->external_reference = $createReceiptDTO->reference;
        $receipt->payment_method_id = $createReceiptDTO->payment_method;
        $receipt->real_account_id = $createReceiptDTO->real_account;
        $receipt->title = $createReceiptDTO->description;
        $receipt->client_id = $createReceiptDTO->payee;
        $receipt->status = 'Pending';
        $receipt->receipt_type = 'single';
    }
}
