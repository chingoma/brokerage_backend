<?php

namespace Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountPlain;
use App\Models\Accounting\RealAccount;
use App\Models\Accounting\Transaction;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Modules\Payments\DTOs\CreatePaymentTransactionDTO;
use Modules\Payments\Pipes\CreatePaymentPipe;
use Modules\Payments\Pipes\CreatePaymentTransactionPipe;
use Modules\Payments\Pipes\CreateSimpleTransactionPaymentPipe;
use Modules\Wallet\Entities\Wallet;
use Throwable;

class PaymentsController extends Controller
{
    public function meta_data(): JsonResponse
    {
        try {
            $response['customers'] = DB::table('users')->select(['email', 'name', 'id'])->get();
            $response['real_accounts'] = RealAccount::select(['id', 'account_name', 'bank_name'])->get();
            $response['categories'] = DB::table('account_categories')
                ->whereNull('deleted_at')
                ->select(['id', 'name'])
                ->where('type', 'payment')
                ->get();
            $response['accounts'] = AccountPlain::select(['id', 'name'])->get();
            $response['paymentMethods'] = PaymentMethod::select(['name', 'id'])->whereNull('deleted_at')->get();

            return response()->json($response);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_multiple(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            if (! empty($request->items) && is_array(json_decode($request->items))) {
                foreach (json_decode($request->items) as $item) {
                    $json = json_encode($item);
                    $requestData = CreatePaymentTransactionDTO::fromJson($json);
                    $pipes = [
                        CreatePaymentTransactionPipe::class,
                        CreatePaymentPipe::class,
                        CreateSimpleTransactionpaymentPipe::class,
                    ];
                    app(Pipeline::class)
                        ->send($requestData)
                        ->through($pipes)
                        ->then(function ($simpleTransaction) {
                            return $simpleTransaction;
                        });
                }
            }

            DB::commit();

            return $this->payments($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $requestData = CreatepaymentTransactionDTO::fromRequest($request);

            $wallet = Wallet::firstOrCreate(['user_id' => $requestData->payee]);
            if ($wallet->available_balance < str_ireplace(',', '', $requestData->amount)) {
                return $this->onErrorResponse('Amount you are trying to '.strtoupper($requestData->description).' is greater than available balance '.number_format($wallet->available_balance));
            }

            $pipes = [
                CreatePaymentTransactionPipe::class,
                CreatePaymentPipe::class,
                CreateSimpleTransactionpaymentPipe::class,
            ];
            DB::beginTransaction();
            app(Pipeline::class)
                ->send($requestData)
                ->through($pipes)
                ->then(function ($simpleTransaction) {
                    return $simpleTransaction;
                });
            DB::commit();

            return $this->payments($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function payments(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $payments = DB::table('payments')
                ->select(['payments.particulars', 'payments.trans_id', 'payments.amount', 'payments.date', 'payments.status', 'payments.uid', 'users.name', 'payments.client_id'])
                ->latest('payments.created_at')
                ->leftJoin('users', 'payments.client_id', '=', 'users.id')
                ->paginate($per_page);

            //            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            //            $payments = Transaction::payments()->latest("transaction_date")->paginate($per_page);
            //
            return response()->json($payments);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
