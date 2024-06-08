<?php

namespace App\Http\Controllers\Accounting;

use App\Data\PusherEventData;
use App\Events\SendNotification;
use App\Helpers\Helper;
use App\Helpers\Pdfs\PaymentPdf;
use App\Helpers\Pdfs\ReceiptPdf;
use App\Http\Controllers\Controller;
use App\Jobs\Statements\UpdateCustomerStatements;
use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\AccountPlain;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\TransactionFile;
use App\Models\DealingSheet;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Entities\CustomerPlain;
use Modules\Orders\Entities\Order;
use Throwable;

class TransactionsController extends Controller
{
    #[NoReturn]
    public function printReceipt(Request $request)
    {
        $transaction = Transaction::find($request->id);
        $pdf = new ReceiptPdf();
        $fullPath = $pdf->create($transaction);
        $this->viewFile($fullPath, $pdf->file);
    }

    #[NoReturn]
    public function printPayment(Request $request)
    {
        $transaction = Transaction::find($request->id);
        $pdf = new PaymentPdf();
        $fullPath = $pdf->create($transaction);
        $this->viewFile($fullPath, $pdf->file);
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

            return $this->all_transactions($request);
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
                    //                    dispatch(new UpdateCustomerStatements($transaction->client_id));
                }
            }

            DB::commit();

            return $this->all_transactions($request);
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
                    //                    DealingSheet::where('slip_no', $transaction->reference)->update(['closed' => "no"]);
                    //                    Order::where('id', $transaction->order_id)->update(['closed' => "no"]);
                    UpdateCustomerStatements::dispatchAfterResponse($transaction->client_id);

                }
            }

            DB::commit();

            return $this->all_transactions($request);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function filter(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');

            $query = Transaction::latest('transaction_date')->groupBy('reference')->orderBy('uid', 'desc');

            if (! empty($request->status)) {
                $query->whereStatus($request->status);
            }

            if (! empty($request->client)) {
                $query = $query->where('client_id', $request->client);
            }

            if (! empty($request->from) && ! empty($request->end)) {
                $query = $query->whereDate('transaction_date', '>=', date('Y-m-d', strtotime($request->from)))
                    ->whereDate('transaction_date', '<=', date('Y-m-d', strtotime($request->end)));
            }

            $payments = $query->paginate($per_page);

            return response()->json($payments);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settings_data(): JsonResponse
    {
        try {
            $response['categories'] = AccountCategory::where('type', 'payment')->get();
            $response['accounts'] = AccountPlain::get();
            $response['customers'] = CustomerPlain::get();
            $response['receiptMethods'] = PaymentMethod::get();

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete_document(Request $request): JsonResponse
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

            return $this->onErrorResponse($ex->getMessage());
        }
    }

    public function create_document(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            if ($request->hasfile('files')) {
                foreach ($request->file('files') as $key => $file) {
                    $data = new TransactionFile();
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

            return $this->onErrorResponse($ex->getMessage());
        }
    }

    public function update_status(Request $request): JsonResponse
    {
        $transaction = Transaction::where('id', $request->id)->orWhere('reference', $request->id)->firstOrFail();
        try {
            DB::beginTransaction();
            Transaction::where('reference', $transaction->reference)->update(['status' => strtolower($request->status)]);
            if (strtolower($request->status) == 'approved') {
                if ($transaction->updated_by == $request->header('id')) {
                    return response()->json(['message' => 'Maker Checker Failed'], 400);
                }
            }

            DB::commit();

            UpdateCustomerStatements::dispatchAfterResponse($transaction->client_id);

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function update_transaction(Request $request): JsonResponse
    {
        $transaction = Transaction::findOrFail($request->id);
        $transaction->description = $request->description;
        $transaction->title = $request->description;
        try {
            DB::beginTransaction();
            $transaction->save();
            DB::commit();

            return response()->json($transaction);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => 'Failed to update transaction'], 500);
        }

    }

    public function new_transactions(Request $request): JsonResponse
    {
        try {
            $transaction = Transaction::latest('transaction_date')->where('status', 'Pending')->paginate(env('PERPAGE'));

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_transactions_type_status(Request $request): JsonResponse
    {
        try {
            $transaction = Transaction::latest('transaction_date')->where('type', $request->type)->where('status', $request->status)->paginate(env('PERPAGE'));

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_transactions_status(Request $request): JsonResponse
    {
        try {
            $transaction = Transaction::latest('created_at')->where('status', $request->status)->paginate(env('PERPAGE'));

            return response()->json($transaction, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function account_transactions(Request $request): JsonResponse
    {
        try {
            $transaction = Transaction::where('account_id', $request->account_id)->latest('transaction_date')->where('status', 'approved')->paginate(env('PERPAGE'));

            return response()->json($transaction);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_transactions(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $transaction = Transaction::latest('created_at')
              //  ->where("status","Pending")
                ->whereNotIn('title', ['Fidelity Fee', 'VAT', 'CMSA Fee', 'CDS Fee', 'DSE Fee'])
                ->whereNotNull('title')
                ->orderBy('transaction_date', 'desc')
                ->groupBy('reference')
                ->paginate($per_page);

            return response()->json($transaction);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function reference(Request $request): JsonResponse
    {
        try {
            $transactions = Transaction::latest('transaction_date')
                ->where('reference', $request->id)
                ->paginate(env('PERPAGE'));

            $sumDebit = Transaction::where('reference', $request->id)->groupBy('reference')->sum('debit');
            $sumCredit = Transaction::where('reference', $request->id)->groupBy('reference')->sum('credit');
            $transactions->credit = $sumCredit;
            $transactions->debit = $sumDebit;

            return response()->json($transactions);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function transaction(Request $request): JsonResponse
    {
        try {
            $transaction = Transaction::find($request->id);

            return response()->json($transaction);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_transaction(Request $request): JsonResponse
    {
        $transaction = new Transaction();
        $transaction->amount = $request->amount;
        $transaction->type = $request->type;
        $transaction->status = 'Pending';
        $transaction->nature = $request->nature;
        $transaction->order_id = $request->order_id;
        $transaction->financial_year_id = Helper::business()->financial_year;
        $transaction->client_id = $request->customer;

        try {
            DB::beginTransaction();
            $transaction->save();
            $transactions = Transaction::orderBy('id', 'desc')->paginate(env('PERPAGE'));
            DB::commit();

            $event = new PusherEventData();
            $event->message = 'New Transaction created';
            $event->title = 'A new Transaction created';
            event(new SendNotification($event));

            return response()->json($transactions, 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return $this->onErrorResponse($ex->getMessage());
        }
    }
}
