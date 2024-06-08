<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AccountReconcileController extends Controller
{
    public function reconcile_selected(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if (! empty($request->items)) {
                foreach ($request->items as $item) {
                    $transaction = Transaction::find($item);
                    Transaction::where('reference', $transaction->reference)
                        ->update(['reconciled' => 'yes', 'real_account_id' => $request->real_account]);
                }
            }

            DB::commit();

            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $list = Transaction::where('category', 'payment')->where('reconciled', 'no')->groupBy('reference')->paginate($per_page);

            return response()->json($list);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function reconciled(): JsonResponse
    {
        $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
        $list = Transaction::where('reconciled', 'yes')->where('status', 'approved')->whereNotNull('real_account_id')->groupBy('reference')->paginate($per_page);

        return response()->json($list);
    }

    public function un_reconciled(): JsonResponse
    {
        $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
        $list = Transaction::where('reconciled', 'no')->where('status', 'approved')->groupBy('reference')->paginate($per_page);

        return response()->json($list);
    }

    public function un_reconcile(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if (! empty($request->items)) {
                foreach ($request->items as $item) {
                    $transaction = Transaction::find($item);
                    Transaction::where('reference', $transaction->reference)

                        ->update(['reconciled' => 'no', 'real_account_id' => null]);
                }
            }

            DB::commit();

            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $list = Transaction::where('reconciled', 'yes')->whereNotNull('real_account_id')->groupBy('reference')->paginate($per_page);

            return response()->json($list);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function stats(): JsonResponse
    {
        $stats['pending'] = Transaction::where('reconciled', 'no')->where('status', 'approved')->groupBy('reference')->get()->count();
        $stats['payments'] = Transaction::where('category', 'payment')->where('status', 'approved')->where('reconciled', 'no')->groupBy('reference')->get()->count();
        $stats['receipts'] = Transaction::where('category', 'receipt')->where('status', 'approved')->where('reconciled', 'no')->groupBy('reference')->get()->count();
        $stats['orders'] = Transaction::where('category', 'order')->where('status', 'approved')->where('reconciled', 'no')->groupBy('reference')->get()->count();
        $stats['total'] = Transaction::where('status', 'approved')->groupBy('reference')->get()->count();

        return response()->json($stats);
    }

    public function reconcile_payments_selected(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if (! empty($request->items)) {
                foreach ($request->items as $item) {
                    $transaction = Transaction::find($item);
                    Transaction::where('reference', $transaction->reference)

                        ->update(['reconciled' => 'yes', 'real_account_id' => $request->real_account]);
                }
            }

            DB::commit();

            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $list = Transaction::where('category', 'payment')->where('reconciled', 'no')->groupBy('reference')->paginate($per_page);

            return response()->json($list);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function reconcile_receipts_selected(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if (! empty($request->items)) {
                foreach ($request->items as $item) {
                    $transaction = Transaction::find($item);
                    Transaction::where('reference', $transaction->reference)

                        ->update(['reconciled' => 'yes', 'real_account_id' => $request->real_account]);
                }
            }

            DB::commit();

            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $list = Transaction::where('category', 'payment')->where('reconciled', 'no')->groupBy('reference')->paginate($per_page);

            return response()->json($list);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function reconcile_orders_selected(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if (! empty($request->items)) {
                foreach ($request->items as $item) {
                    $transaction = Transaction::find($item);
                    Transaction::where('reference', $transaction->reference)

                        ->update(['reconciled' => 'yes', 'real_account_id' => $request->real_account]);
                }
            }

            DB::commit();

            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $list = Transaction::where('category', 'order')->where('reconciled', 'no')->groupBy('reference')->paginate($per_page);

            return response()->json($list);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function reconcile_payments(): JsonResponse
    {
        $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
        $list = Transaction::where('category', 'payment')->where('reconciled', 'no')->groupBy('reference')->paginate($per_page);

        return response()->json($list);
    }

    public function reconcile_receipts(): JsonResponse
    {
        $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
        $list = Transaction::where('category', 'receipt')->where('reconciled', 'no')->groupBy('reference')->paginate($per_page);

        return response()->json($list);
    }

    public function reconcile_orders(): JsonResponse
    {
        $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
        $list = Transaction::where('category', 'order')->where('reconciled', 'no')->groupBy('reference')->paginate($per_page);

        return response()->json($list);
    }
}
