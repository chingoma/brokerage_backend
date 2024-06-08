<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountClass;
use App\Models\Accounting\Transaction;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Contracts\Activity;
use Throwable;

class VouchersController extends Controller
{
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $reference = $this->generateReference($request->reference);

            if (! empty($request->items) && is_array(json_decode($request->items))) {
                foreach (json_decode($request->items) as $key => $item) {
                    $account = Account::find($item->account);
                    $class = AccountClass::find($account->class_id);
                    $voucher = new Transaction();
                    $this->setBusinessYear($voucher);
                    $this->setTransactionAction($voucher, $item);
                    $voucher->title = $request->title;
                    $voucher->transaction_date = $request->transaction_date;
                    $voucher->reference = $reference;
                    $voucher->status = $item->status;
                    $voucher->description = $item->description;
                    $voucher->client_id = $item->payee;
                    $voucher->category = 'Voucher';
                    $voucher->account_id = $item->account;
                    $voucher->class_id = $class->id;
                    $voucher->save();
                }
            }

            DB::commit();
            activity()
                ->on($voucher)
                ->by(auth()->user())
                ->event('created')
                ->tap(function (Activity $activity) {
                    $activity->business_id = auth()->user()->business_id;
                    $activity->branch_id = auth()->user()->branch_id;
                })
                ->log('created new voucher');

            return $this->vouchers();

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

            $reference = $request->reference;
            Transaction::where('category', 'Voucher')->where('reference', $reference)->delete();
            if (! empty($request->items) && is_array(json_decode($request->items))) {
                foreach (json_decode($request->items) as $key => $item) {
                    $account = Account::find($item->account);
                    $class = AccountClass::find($account->class_id);
                    $voucher = new Transaction();
                    $this->setBusinessYear($voucher);
                    $this->setTransactionAction($voucher, $item);
                    $voucher->title = $request->title;
                    $voucher->transaction_date = $request->transaction_date;
                    $voucher->reference = $reference;
                    $voucher->status = $item->status;
                    $voucher->description = $item->description;
                    $voucher->client_id = $item->payee;
                    $voucher->category = 'Voucher';
                    $voucher->account_id = $item->account;
                    $voucher->class_id = $class->id;
                    $voucher->save();
                }
            }

            DB::commit();
            activity()
                ->on($voucher)
                ->by(auth()->user())
                ->event('created')
                ->tap(function (Activity $activity) {
                    $activity->business_id = auth()->user()->business_id;
                    $activity->branch_id = auth()->user()->branch_id;
                })
                ->log('created new voucher');

            return $this->vouchers();

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function voucher(Request $request)
    {
        try {
            $voucher = Transaction::where('category', 'Voucher')->where('reference', $request->reference)->first();
            $response['items'] = Transaction::where('reference', $request->reference)->orderBy('id', 'desc')->get();
            $response['title'] = $voucher->title;
            $response['payee'] = $voucher->client;
            $response['transaction_date'] = $voucher->transaction_date;
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
            $voucher = Account::find($request->id);
            $voucher->delete();

            activity()
                ->on($voucher)
                ->by(auth()->user())
                ->event('deleted')
                ->tap(function (Activity $activity) {
                    $activity->business_id = auth()->user()->business_id;
                    $activity->branch_id = auth()->user()->branch_id;
                })
                ->log('deleted voucher:  '.$voucher->name);

            return $this->vouchers();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function vouchers()
    {
        try {
            $vouchers = Transaction::vouchers()->orderBy('id', 'desc')->paginate();

            return response()->json($vouchers, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settings_data()
    {
        try {
            $response['accounts'] = Account::get();
            $response['customers'] = User::customers()->get();
            $response['payees'] = User::payees()->get();
            $response['paymentMethods'] = PaymentMethod::get();

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
