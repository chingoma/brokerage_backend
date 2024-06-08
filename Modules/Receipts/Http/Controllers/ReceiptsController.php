<?php

namespace Modules\Receipts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountPlain;
use App\Models\Accounting\RealAccount;
use App\Models\Accounting\Transaction;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Modules\Receipts\DTOs\CreateReceiptTransactionDTO;
use Modules\Receipts\Pipes\CreateReceiptPipe;
use Modules\Receipts\Pipes\CreateReceiptTransactionPipe;
use Modules\Receipts\Pipes\CreateSimpleTransactionReceiptPipe;
use Throwable;

class ReceiptsController extends Controller
{
    public function meta_data(): JsonResponse
    {
        try {
            $response['customers'] = DB::table('users')->select(['email', 'name', 'id'])->get();
            $response['real_accounts'] = RealAccount::select(['id', 'account_name', 'bank_name'])->get();
            $response['categories'] = DB::table('account_categories')->whereNull('deleted_at')->select(['id', 'name'])->where('type', 'receipt')->get();
            $response['accounts'] = AccountPlain::select(['id', 'name'])->get();
            $response['receiptMethods'] = PaymentMethod::select(['name', 'id'])->whereNull('deleted_at')->get();

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
                    $requestData = CreateReceiptTransactionDTO::fromJson($json);
                    $pipes = [
                        CreateReceiptTransactionPipe::class,
                        CreateReceiptPipe::class,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $requestData = CreateReceiptTransactionDTO::fromRequest($request);
            $pipes = [
                CreateReceiptTransactionPipe::class,
                CreateReceiptPipe::class,
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

    public function receipts(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $receipts = DB::table('receipts')
                ->select(['receipts.particulars', 'receipts.trans_id', 'receipts.amount', 'receipts.date', 'receipts.status', 'receipts.uid', 'users.name', 'receipts.client_id'])
                ->latest('receipts.created_at')
                ->leftJoin('users', 'receipts.client_id', '=', 'users.id')
                ->paginate($per_page);

            //            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            //            $receipts = Transaction::receipts()->latest("transaction_date")->paginate($per_page);

            return response()->json($receipts);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
