<?php

namespace App\Http\Controllers\Accounting;

use App\Data\PusherEventData;
use App\Events\SendNotification;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountSetting;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\TransactionFile;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClientTransactionsController extends Controller
{
    public function delete_document(Request $request)
    {

        try {
            DB::beginTransaction();

            $data = TransactionFile::find($request->id);
            $copy = clone $data;
            if (! empty($data)) {
                $data->delete();
            }
            DB::commit();

            return response()->json(Transaction::find($per_page_id), 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'creation failed '.$ex->getMessage()], 500);
        }
    }

    public function create_document(Request $request)
    {

        try {
            DB::beginTransaction();

            if ($request->hasfile('files')) {
                foreach ($request->file('files') as $key => $file) {
                    $data = new TransactionFile();
                    $data->name = $per_page_names[$key];
                    $data->transaction_id = $per_page_id;
                    $data->business_id = $request->user()->profile->business_id;
                    $path = $file->store('public/business/profiles');
                    $data->file_id = str_ireplace('public/business/profiles/', '', $path);
                    $data->extension = $file->extension();
                    $data->path = str_ireplace('public/', '', $path);
                    $data->save();
                }

            }

            DB::commit();

            return response()->json(Transaction::find($per_page_id), 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'creation failed '.$ex->getMessage()], 500);
        }
    }

    public function update_status(Request $request)
    {
        $transaction = Transaction::find($request->id);
        $transaction->status = $request->status;
        try {
            DB::beginTransaction();
            $transaction->save();
            DB::commit();

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => 'Failed to update profile'], 500);
        }

    }

    public function update_transaction(Request $request)
    {
        $transaction = Transaction::find($request->id);
        $transaction->amount = $request->amount;
        $transaction->type = $request->type;
        $transaction->nature = $request->nature;
        $transaction->client_id = $request->customer;
        try {
            DB::beginTransaction();
            $transaction->save();
            DB::commit();

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => 'Failed to update transaction'], 500);
        }

    }

    public function new_transactions(Request $request)
    {
        try {
            $transaction = Transaction::where('client_id', auth()->user()->id)->where('status', 'Pending')->paginate();

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_transactions_type_status(Request $request)
    {
        try {
            $transaction = Transaction::where('client_id', auth()->user()->id)->where('type', $request->type)->where('status', $request->status)->paginate();

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_transactions_status(Request $request)
    {
        try {
            $transaction = Transaction::where('client_id', auth()->user()->id)->where('status', $request->status)->paginate();

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_transactions(Request $request)
    {
        try {
            $transactions = $this->_transactions();

            return response()->json($transactions, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function transaction(Request $request)
    {
        try {
            $transaction = Transaction::find($request->id);

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settings(Request $request)
    {
        try {
            $settings['paymentMethods'] = PaymentMethod::get();

            return response()->json($settings, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_transaction(Request $request)
    {
        $setting = AccountSetting::first();
        try {
            DB::beginTransaction();
            $transaction = new Transaction();
            $transaction->transaction_date = now()->toDateString();
            $transaction->amount = Helper::number($request->amount);
            $transaction->payment_method_id = $request->payment_method;
            $transaction->account_id = Helper::account($setting->customer_liability_account)->id;
            $transaction->credit = Helper::number($request->amount);
            $transaction->description = $request->description;
            $transaction->reference = $request->reference;
            $transaction->debit = 0;
            $transaction->title = 'Client Deposit';
            $transaction->category = 'Receipt';
            $transaction->status = 'Pending';
            $transaction->action = 'Credit';
            $transaction->customer_action = 'Deposit';
            $transaction->financial_year_id = Helper::business()->financial_year;
            $transaction->client_id = auth()->user()->id;
            $transaction->business_id = auth()->user()->profile->business_id;
            $transaction->branch_id = auth()->user()->profile->branch_id;
            $transaction->payment_method_id = $request->payment_method;
            $transaction->save();
            $firstId = $transaction->id;

            $transaction = new Transaction();
            $transaction->title = 'Client Deposit';
            $transaction->transaction_date = now()->toDateString();
            $transaction->amount = Helper::number($request->amount);
            $transaction->payment_method_id = $request->payment_method;
            $transaction->account_id = Helper::account($setting->customer_cash_account)->id;
            $transaction->debit = Helper::number($request->amount);
            $transaction->description = $request->description;
            $transaction->reference = $request->reference;
            $transaction->credit = 0;
            $transaction->category = 'Receipt';
            $transaction->status = 'Pending';
            $transaction->action = 'Debit';
            $transaction->customer_action = 'Deposit';
            $transaction->financial_year_id = Helper::business()->financial_year;
            $transaction->client_id = auth()->user()->id;
            $transaction->business_id = auth()->user()->profile->business_id;
            $transaction->branch_id = auth()->user()->profile->branch_id;
            $transaction->payment_method_id = $request->payment_method;
            $transaction->save();
            $secondId = $transaction->id;
            if ($request->hasfile('file')) {

                $path = $request->file('file')->store('public/business/profiles');

                $data = new TransactionFile();
                $data->name = 'Transaction attachment';
                $data->transaction_id = $firstId;
                $data->business_id = $request->user()->profile->business_id;
                $data->file_id = str_ireplace('public/business/profiles/', '', $path);
                $data->extension = $request->file('file')->extension();
                $data->path = str_ireplace('public/', '', $path);
                $data->save();

                $data = new TransactionFile();
                $data->name = 'Transaction attachment';
                $data->transaction_id = $secondId;
                $data->business_id = $request->user()->profile->business_id;
                $data->file_id = str_ireplace('public/business/profiles/', '', $path);
                $data->extension = $request->file('file')->extension();
                $data->path = str_ireplace('public/', '', $path);
                $data->save();

            }
            //            else{
            //                return response()->json(['status' => false, 'message' => "We could not find attachment "], 500);
            //            }

            $transactions = $this->_transactions();

            DB::commit();

            $event = new PusherEventData();
            $event->message = 'New Transaction created';
            $event->title = 'A new Transaction created';
            event(new SendNotification($event));

            return response()->json($transactions, 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'registration failed failed '.$ex->getMessage()], 500);
        }
    }

    private function _transactions()
    {
        $setting = AccountSetting::first();
        $account_id = Helper::account($setting->customer_liability_account)->id;

        return Transaction::orderBy('id', 'desc')
            ->whereNotNull('customer_action')
            ->where('client_id', auth()->user()->id)
            ->where('account_id', $account_id)
            ->paginate(env('PERPAGE'));
    }
}
