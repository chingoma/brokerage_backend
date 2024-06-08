<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\AccountClass;
use App\Models\Accounting\RealAccount;
use App\Models\Accounting\Transaction;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Entities\CustomerPlain;
use Throwable;

class JournalsController extends Controller
{
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            if (! empty($request->items) && is_array(json_decode($request->items))) {
                foreach (json_decode($request->items) as $key => $item) {
                    $category = AccountCategory::find($item->category);
                    $reference = $this->generateReference($request->reference);
                    $debit_account = Account::find($item->debit_account);
                    $class = AccountClass::find($debit_account->class_id);
                    $journal = new Transaction();
                    $this->setBusinessYear($journal);
                    $this->setTransactionJournal($journal, $item, 'Debit');
                    $journal->title = $request->title;
                    $journal->reference = $reference;
                    $journal->transaction_date = date('Y-m-d', strtotime($request->transaction_date));
                    $journal->client_id = $item->payee ?? '';
                    $journal->status = 'pending';
                    $journal->category = $category->type;
                    $journal->account_category_id = $category->id;
                    $journal->account_id = $item->debit_account;
                    $journal->class_id = $class->id;
                    $journal->save();
                    Helper::transactionUID($journal);
                    $credit_account = Account::find($item->credit_account);
                    $class = AccountClass::find($credit_account->class_id);
                    $journal = new Transaction();
                    $this->setBusinessYear($journal);
                    $this->setTransactionJournal($journal, $item, 'Credit');
                    $journal->title = $request->title;
                    $journal->reference = $reference;
                    $journal->status = 'Pending';
                    $journal->transaction_date = $request->transaction_date;
                    $journal->client_id = $item->payee ?? '';
                    $journal->category = $category->type;
                    $journal->account_category_id = $category->id;
                    $journal->account_id = $item->credit_account;
                    $journal->class_id = $class->id;
                    $journal->save();
                    Helper::transactionUID($journal);
                }
            }

            DB::commit();

            return $this->journals();

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            DB::beginTransaction();
            $reference = $this->generateReference($request->reference);
            Transaction::whereNotNull('is_journal')->where('reference', $reference)->delete();
            if (! empty($request->items) && is_array(json_decode($request->items))) {
                foreach (json_decode($request->items) as $key => $item) {
                    $category = AccountCategory::find($item->category);
                    $debit_account = Account::find($item->account);
                    $class = AccountClass::find($debit_account->class_id);
                    $journal = new Transaction();
                    $this->setBusinessYear($journal);
                    $this->setTransactionJournal($journal, $item, $item->action);
                    $journal->title = $request->title;
                    $journal->transaction_date = date('Y-m-d', strtotime($request->transaction_date));
                    $journal->reference = $reference;
                    $journal->client_id = $item->payee ?? '';
                    $journal->status = 'Pending';
                    $journal->category = $category->type;
                    $journal->account_category_id = $category->id;
                    $journal->account_id = $item->account;
                    $journal->class_id = $class->id;
                    $journal->real_account_id = $item->real_account;
                    $journal->save();
                }
            }

            DB::commit();

            return $this->journals();

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function journal(Request $request)
    {
        try {
            $journal = Transaction::whereNotNull('is_journal')->where('reference', $request->reference)->first();
            $response['items'] = Transaction::where('reference', $request->reference)->orderBy('id', 'desc')->get();
            $response['title'] = $journal->title;
            $response['payee'] = $journal->client;
            $response['transaction_date'] = $journal->transaction_date;
            $response['reference'] = $request->reference;

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try {
            $journal = Account::find($request->id);
            $journal->delete();

            return $this->journals();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function journals()
    {
        try {
            $journals = Transaction::journals()->orderBy('id', 'desc')->paginate(env('PERPAGE'));

            return response()->json($journals);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settings_data(): JsonResponse
    {
        try {
            $response['categories'] = AccountCategory::where('type', 'journal')->get();
            $response['accounts'] = Account::get();
            $response['real_accounts'] = RealAccount::get();
            $response['customers'] = CustomerPlain::get();
            $response['paymentMethods'] = PaymentMethod::get();

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
