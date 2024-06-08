<?php

namespace Modules\Transactions\Http\Controllers;

use App\Helpers\Clients\UsersHelper;
use App\Helpers\Pdfs\StatementPdf;
use App\Http\Controllers\Controller;
use App\Models\Accounting\AllTransaction;
use App\Models\Accounting\Transaction;
use App\Models\Statement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Entities\SimpleTransaction;
use Modules\FlexcubeAPI\Helpers\FlexcubeFunctions;
use Modules\FlexcubeAPI\Helpers\FlexcubePrepareFunctions;
use Modules\Payments\Entities\Payment;
use Modules\Receipts\Entities\Receipt;
use Modules\Wallet\Entities\AvailableWalletHistory;
use Modules\Wallet\Entities\BondsOnHold;
use Modules\Wallet\Entities\EquitiesOnHold;
use Modules\Wallet\Entities\PaymentsOnHold;
use Modules\Wallet\Entities\ReceiptsOnHold;
use Modules\Wallet\Entities\Wallet;
use Throwable;

class TransactionsController extends Controller
{
    public function reference(Request $request): JsonResponse
    {
        try {
            $transactions = DB::table('transactions')
                ->latest('transaction_date')
                ->select(['transactions.credit', 'transactions.account_id', 'transactions.debit', 'accounts.name as account_name'])
                ->where('reference', $request->id)
                ->leftJoin('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->get();

            $sumDebit = DB::table('transactions')->where('reference', $request->id)->groupBy('reference')->sum('debit');
            $sumCredit = DB::table('transactions')->where('reference', $request->id)->groupBy('reference')->sum('credit');
            $data = new \stdClass();
            $data->transactions = $transactions;
            $data->credit = $sumCredit;
            $data->debit = $sumDebit;

            return response()->json($data);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function transaction(Request $request): JsonResponse
    {
        try {
            //            $transaction = DB::table("transactions")
            //                ->select(['payment_methods.name as method_name','transactions.client_id','transactions.category','transactions.reference','transactions.transaction_date','transactions.category','transactions.status','transactions.title'])
            //                ->leftJoin("payment_methods",'transactions.payment_method_id','=','payment_methods.id')
            //                ->where("transactions.id",$request->id)->first();
            //            if(strtolower($transaction->category) == "order"){
            //                $transaction = Transaction::find($request->id);
            //            }
            //            if(strtolower($transaction->category) == "bond"){
            //                $transaction = Transaction::find($request->id);
            //            }
            //            if(strtolower($transaction->category) == "custodian"){
            //                $transaction = Transaction::find($request->id);
            //            }

            $transaction = DB::table('transactions')->select('category')->find($request->id);

            if (strtolower($transaction->category) == 'bond') {
                $transaction = DB::table('transactions')
                    ->select([
                        'payment_methods.name as method_name',
                        'transactions.id',
                        'transactions.flexcube_status',
                        'transactions.amount',
                        'transactions.uid',
                        'transactions.external_reference',
                        'transactions.client_id',
                        'transactions.category',
                        'transactions.reference',
                        'transactions.transaction_date',
                        'transactions.category',
                        'transactions.status',
                        'transactions.title',
                        'bond_orders.type',
                        'bond_orders.uid as order_uid',
                        'users.name as payee',
                        'bonds.security_name as security',
                    ])
                    ->leftJoin('users', 'transactions.client_id', '=', 'users.id')
                    ->leftJoin('bond_executions', 'transactions.reference', '=', 'bond_executions.slip_no')
                    ->leftJoin('bond_orders', 'transactions.order_id', '=', 'bond_orders.id')
                    ->leftJoin('bonds', 'bond_orders.bond_id', '=', 'bonds.id')
                    ->leftJoin('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
                    ->where('transactions.id', $request->id)->first();
            } elseif (strtolower($transaction->category) == 'order') {
                $transaction = DB::table('transactions')
                    ->select([
                        'payment_methods.name as method_name',
                        'transactions.id',
                        'transactions.flexcube_status',
                        'transactions.amount',
                        'transactions.uid',
                        'transactions.external_reference',
                        'transactions.client_id',
                        'transactions.category',
                        'transactions.reference',
                        'transactions.transaction_date',
                        'transactions.category',
                        'transactions.status',
                        'transactions.title',
                        'orders.type',
                        'orders.uid as order_uid',
                        'users.name as payee',
                        'securities.name as security',
                    ])
                    ->leftJoin('users', 'transactions.client_id', '=', 'users.id')
                    ->leftJoin('dealing_sheets', 'transactions.reference', '=', 'dealing_sheets.slip_no')
                    ->leftJoin('orders', 'transactions.order_id', '=', 'orders.id')
                    ->leftJoin('securities', 'orders.security_id', '=', 'securities.id')
                    ->leftJoin('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
                    ->where('transactions.id', $request->id)->first();
            } elseif (strtolower($transaction->category) == 'custodian') {
                $transaction = DB::table('transactions')
                    ->select([
                        'payment_methods.name as method_name',
                        'transactions.flexcube_status',
                        'transactions.amount',
                        'transactions.id',
                        'transactions.uid',
                        'transactions.external_reference',
                        'transactions.client_id',
                        'transactions.category',
                        'transactions.reference',
                        'transactions.transaction_date',
                        'transactions.category',
                        'transactions.status',
                        'transactions.title',
                        'bond_orders.type',
                        'bond_orders.uid as order_uid',
                        'users.name as payee',
                        'bonds.security_name as security',
                    ])
                    ->leftJoin('users', 'transactions.client_id', '=', 'users.id')
                    ->leftJoin('bond_executions', 'transactions.reference', '=', 'bond_executions.slip_no')
                    ->leftJoin('bond_orders', 'transactions.order_id', '=', 'bond_orders.id')
                    ->leftJoin('bonds', 'bond_orders.bond_id', '=', 'bonds.id')
                    ->leftJoin('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
                    ->where('transactions.id', $request->id)->first();
            } else {
                $transaction = DB::table('transactions')
                    ->select([
                        'payment_methods.name as method_name',
                        'transactions.id',
                        'transactions.flexcube_status',
                        'transactions.amount',
                        'transactions.uid',
                        'transactions.external_reference',
                        'transactions.client_id',
                        'transactions.category',
                        'transactions.reference',
                        'transactions.transaction_date',
                        'transactions.category',
                        'transactions.status',
                        'transactions.title',
                        'orders.type',
                        'orders.uid as order_uid',
                        'users.name as payee',
                        'securities.name as security',
                    ])
                    ->leftJoin('users', 'transactions.client_id', '=', 'users.id')
                    ->leftJoin('dealing_sheets', 'transactions.reference', '=', 'dealing_sheets.slip_no')
                    ->leftJoin('orders', 'transactions.order_id', '=', 'orders.id')
                    ->leftJoin('securities', 'orders.security_id', '=', 'securities.id')
                    ->leftJoin('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
                    ->where('transactions.id', $request->id)->first();
            }

            return response()->json($transaction);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $transactions = AllTransaction::latest('transactions.created_at')
                ->select(['transactions.*'])
                ->selectRaw('transactions.transaction_date as date')
                ->selectRaw('transactions.category as trans_category')
                ->whereNotIn('transactions.title', ['Fidelity Fee', 'VAT', 'CMSA Fee', 'CDS Fee', 'DSE Fee'])
                ->whereNotNull('transactions.title')
                ->groupBy('transactions.reference')
                ->paginate($per_page);

            return response()->json($transactions);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function pending(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = DB::table("simple_transactions")
                ->select(['simple_transactions.*','users.name as payee'])
                ->whereNull("simple_transactions.deleted_at")
                ->latest('simple_transactions.created_at')
//                ->where('simple_transactions.status', 'pending')
                ->whereDay('simple_transactions.created_at', now()->toDateTime())
                ->leftJoin("users", "simple_transactions.client_id", "=", "users.id")
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function pending_sync(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = DB::table("simple_transactions")
                ->select(['simple_transactions.*','users.name as payee'])
                ->whereNull("simple_transactions.deleted_at")
                ->where("simple_transactions.flexcube_status","pending")
//                ->where("simple_transactions.trans_category","!=","custodian")
                ->latest('simple_transactions.created_at')
                ->where('simple_transactions.status', 'approved')
                ->leftJoin("users", "simple_transactions.client_id", "=", "users.id")
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function statements(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = Statement::latest('id')
                ->where('status', 'approved')
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function statements_customer(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = Statement::latest()
                ->where('client_id', $request->id)
                ->where('status', 'approved')
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function payments(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = SimpleTransaction::latest('created_at')
                ->where('trans_category', 'payment')
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function receipts(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = SimpleTransaction::latest('created_at')
                ->where('trans_category', 'receipt')
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function equities(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = SimpleTransaction::latest('created_at')
                ->where('trans_category', 'order')
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function bonds(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = SimpleTransaction::latest('created_at')
                ->where('trans_category', 'bond')
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function custodians(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = SimpleTransaction::latest('created_at')
                ->where('trans_category', 'custodian')
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function reject(Request $request): JsonResponse
    {

        try {

            DB::beginTransaction();

            $transaction = Transaction::findOrFail($request->id);

            $statementStatus = Statement::where('trans_reference', $transaction->reference)->first();

            if (! empty($statementStatus)) {
                return $this->onErrorResponse('You can not reject this transaction, it is present in statement table. Error code #TRANS-EXIST-STATEMENT');
            }

            if ($transaction->status == 'approved') {
                return $this->onErrorResponse('You can not reject '.$transaction->status.' transaction Error code #TRANS-APPROVED');
            }

            Transaction::where('reference', $transaction->reference)->update(['status' => 'rejected']);

            $simpleTransaction = SimpleTransaction::where('trans_id', $request->id)->first();
            $simpleTransaction->status = 'rejected';
            $simpleTransaction->save();
            $this->__cascadeReject($transaction);
            DB::commit();

            return response()->json($transaction);

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse('System could not process your request, Contact Support #TRANS-PROCESS-FAIL-0001');
        }

    }

    public function approve(Request $request): JsonResponse
    {

        try {

            DB::beginTransaction();
            $transaction = Transaction::findOrFail($request->id);

            if ($transaction->status != 'pending') {
                return $this->onErrorResponse('You can not approve '.$transaction->status.' transaction');
            }

            $statementStatus = Statement::where('trans_reference', $transaction->reference)->first();

            if (! empty($statementStatus)) {
                Transaction::where('reference', $transaction->reference)->update(['status' => 'approved']);

                $simpleTransaction = SimpleTransaction::where('trans_id', $request->id)->first();
                $simpleTransaction->status = 'approved';
                $simpleTransaction->save();

                $statementStatus->status = 'approved';
                $statementStatus->save();
                DB::commit();
                return response()->json($transaction);
            }

            if ($transaction->updated_by == $request->header('id')) {
                //return response()->json(['message' => 'Maker-Checker process failed'], 400);
            }

            if ($transaction->status != 'pending') {
                return $this->onErrorResponse('You can not approve '.$transaction->status.' transaction');
            }

            Transaction::where('reference', $transaction->reference)->update(['status' => 'approved']);

            $simpleTransaction = SimpleTransaction::where('trans_id', $request->id)->firstOrFail();
            $simpleTransaction->status = 'approved';
            $simpleTransaction->save();

            $this->__cascadeApprove($transaction);

            $this->__updateStatement($simpleTransaction);

            DB::commit();

            $this->__prepare_flexcube_posting($transaction);
            return response()->json($transaction);

        } catch (ModelNotFoundException $exception) {
            return $this->onErrorResponse('System could not process your request, Contact Support #MODEL-NOT-FOUND');
        } catch (Throwable $throwable) {
            report($throwable);
            return $this->onErrorResponse('System could not process your request, Contact Support #TRANS-PROCESS-FAIL-0000');
        }

    }

    public function post_to_flexcube(Request $request): JsonResponse
    {
        try {
//            DB::beginTransaction();
            $transaction = Transaction::findOrFail($request->id);

//            if ($transaction->status != 'approved') {
//                return $this->onErrorResponse('You can not post '.$transaction->status.' transaction');
//            }
//
//            if ($transaction->flexcube_status != 'pending') {
//                return $this->onErrorResponse('You can not post '.$transaction->flexcube_status.' transaction');
//            }

            $statement = Statement::where('trans_reference', $transaction->reference)->first();

//            if (! empty($statement)) {
//
//                Transaction::where('reference', $transaction->reference)->update([
//                    'flexcube_status' => 'synced',
//                    'flexcube_synced_by' => auth()->id(),
//                    'flexcube_synced_at' => now(getenv("TIMEZONE"))->toDateString(),
//                ]);
//
//                SimpleTransaction::where('trans_id', $request->id)->update([
//                    'flexcube_status' => 'synced',
//                    'flexcube_synced_by' => auth()->id(),
//                    'flexcube_synced_at' => now(getenv("TIMEZONE"))->toDateString(),
//                ]);
//
//                $statement->flexcube_status = 'synced';
//                $statement->flexcube_synced_by = auth()->id();
//                $statement->flexcube_synced_at = now(getenv("TIMEZONE"))->toDateString();
//                $statement->save();
//
//                $status = FlexcubeFunctions::prepareAndPortToFlexicube($transaction);
//
//                if($status->status){
//                    DB::commit();
//                    return $this->onSuccessResponse("Transaction posted successfully.");
//                }
//
//                return $this->onErrorResponse($status->message);
//            }

            $this->__prepare_flexcube_posting($transaction);
            return $this->onSuccessResponse("Transaction posted successfully.");

        } catch (ModelNotFoundException $exception) {
            Log::error($exception->getMessage());

            return $this->onErrorResponse('System could not process your request, Contact Support #MODEL-NOT-FOUND');
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
//            return $this->onErrorResponse('System could not process your request, Contact Support #TRANS-PROCESS-FAIL-0000');
        }

    }

    public function do_not_post_to_flexcube(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $transaction = Transaction::findOrFail($request->id);

//            if ($transaction->status != 'approved') {
//                return $this->onErrorResponse('You can not post '.$transaction->status.' transaction');
//            }
//
//            if ($transaction->flexcube_status != 'pending') {
//                return $this->onErrorResponse('You can not post '.$transaction->flexcube_status.' transaction');
//            }

            $statement = Statement::where('trans_reference', $transaction->reference)->first();

//            if (! empty($statement)) {

                Transaction::where('reference', $transaction->reference)->update([
                    'flexcube_status' => 'synced',
                    'flexcube_synced_by' => auth()->id(),
                    'flexcube_synced_at' => now(getenv("TIMEZONE"))->toDateString(),
                ]);

                SimpleTransaction::where('trans_id', $request->id)->update([
                    'flexcube_status' => 'synced',
                    'flexcube_synced_by' => auth()->id(),
                    'flexcube_synced_at' => now(getenv("TIMEZONE"))->toDateString(),
                ]);

                $statement->flexcube_status = 'synced';
                $statement->flexcube_synced_by = auth()->id();
                $statement->flexcube_synced_at = now(getenv("TIMEZONE"))->toDateString();
                $statement->save();

//                return $this->onSuccessResponse("Transaction status changed successfully.");

//            }
              DB::commit();
            return $this->onSuccessResponse("Transaction status changed successfully.");

        }catch (Throwable $throwable) {
            return $this->onErrorResponse($throwable->getMessage());
//            return $this->onErrorResponse('System could not process your request, Contact Support #TRANS-PROCESS-FAIL-0000');
        }

    }

    private function __cascadeApprove(Transaction $transaction)
    {
        if (strtolower($transaction->category) == 'receipt') {
            $receipt = Receipt::where('trans_id', $transaction->id)->first();
            $receipt->status = 'approved';
            $receipt->save();
            ReceiptsOnHold::where('receipt_id', $receipt->id)->delete();
        }

        if (strtolower($transaction->category) == 'payment') {
            $payment = Payment::where('trans_id', $transaction->id)->first();
            $payment->status = 'approved';
            $payment->save();
            PaymentsOnHold::where('payment_id', $payment->id)->delete();
        }

    }

    private function __cascadeReject(Transaction $transaction)
    {
        if (strtolower($transaction->category) == 'receipt') {
            $receipt = Receipt::where('trans_id', $transaction->id)->first();
            $receipt->status = 'rejected';
            $receipt->save();
            $this->updateWallet(user_id: $receipt->client_id);
        }

        if (strtolower($transaction->category) == 'payment') {
            $payment = Payment::where('trans_id', $transaction->id)->first();
            $payment->status = 'rejected';
            $payment->save();
            PaymentsOnHold::where('payment_id', $payment->id)->delete();
            $this->updateWallet($payment->client_id);
        }
    }

    private function __updateStatement(SimpleTransaction $transaction)
    {
        $lastEntry = Statement::where('client_id', $transaction->client_id)
            ->latest('auto')
            ->limit(1)
            ->first();

        $statement = new Statement();
        $statement->trans_id = $transaction->trans_id;
        $statement->trans_category = $transaction->trans_category;
        $statement->trans_reference = $transaction->trans_reference;
        $statement->order_type = $transaction->order_type;
        $statement->order_id = $transaction->order_id;
        $statement->client_id = $transaction->client_id;
        $statement->date = $transaction->date;
        $statement->type = $transaction->type;
        $statement->category = $transaction->category;
        $statement->reference = $transaction->reference;
        $statement->particulars = $transaction->particulars;
        $statement->quantity = $transaction->quantity;
        $statement->price = $transaction->price;
        $statement->debit = $transaction->debit;
        $statement->credit = $transaction->credit;

        if (empty($lastEntry)) {
            $balance = 0;
        } else {
            $balance = $lastEntry->balance;
        }

        if (strtolower($transaction->type) == 'custodian') {
            $statement->balance = $balance;
        } else {
            if (strtolower($transaction->action) == 'credit') {
                $statement->balance = $balance + $transaction->amount;
            } else {
                $statement->balance = $balance - $transaction->amount;
            }
        }

        if ($statement->balance < 0) {
            $state = 'Dr';
        } else {
            $state = 'Cr';
        }

        $statement->state = $state;
        $statement->save();

        // update available balance
        $this->updateWallet($statement->client_id);

        // send transaction notifications
//        self::sendTransactionNotifications(statement: $statement, amount: $transaction->amount, action: $transaction->action);

    }

    private function _updateWalletOrder(SimpleTransaction $transaction)
    {
        $history = new AvailableWalletHistory();
        $history->user_id = $transaction->client_id;
        $history->model_id = $transaction->trans_id;
        $history->category = 'equity';
        $history->amount = $transaction->amount;
        $history->description = 'Release from on hold';
        $transaction->save();
    }

    private function _updateWalletBond(SimpleTransaction $transaction)
    {
        $history = new AvailableWalletHistory();
        $history->user_id = $transaction->client_id;
        $history->model_id = $transaction->trans_id;
        $history->category = 'bond';
        $history->amount = $transaction->amount;
        $history->description = 'Release from on hold';
        $transaction->save();
    }

    private function _updateWalletReceipt(SimpleTransaction $transaction)
    {
        $history = new AvailableWalletHistory();
        $history->user_id = $transaction->client_id;
        $history->model_id = $transaction->trans_id;
        $history->category = 'receipt';
        $history->amount = $transaction->amount;
        $history->description = 'Increased actual and available balance after receipt approval';
        $transaction->save();
    }

    public static function updateWallet($user_id)
    {
        $currentBalance = UsersHelper::wallet_balance($user_id);
        $wallet = Wallet::firstOrCreate(['user_id' => $user_id]);
        $totalPaymentHold = PaymentsOnHold::where('user_id', $user_id)->sum('amount');
        $totalEquityHold = EquitiesOnHold::where('user_id', $user_id)->sum('amount');
        $totalBondHold = BondsOnHold::where('user_id', $user_id)->sum('amount');
        $totalHold = $totalPaymentHold + $totalEquityHold + $totalBondHold;
        $wallet->actual_balance = $currentBalance;
        if ($totalHold < 0) {
            $wallet->available_balance = $currentBalance + $totalHold;
        } else {
            $wallet->available_balance = $currentBalance - $totalHold;
        }
        $wallet->save();
    }

    public static function updateCustomerStatement($transactions, $user)
    {
        if (! empty($transactions)) {
            $pdf = new StatementPdf(false);
            $pdf->create($transactions, $user);
            $statements = $pdf->statement;
            if (! empty($statements)) {
                foreach ($statements as $transaction) {
                    $transaction = (object) $transaction;
                    $statement = new Statement();
                    $statement->trans_id = $transaction->trans_id;
                    $statement->trans_category = $transaction->trans_category;
                    $statement->trans_reference = $transaction->trans_reference;
                    $statement->order_type = $transaction->order_type;
                    $statement->order_id = $transaction->order_id;
                    $statement->client_id = $user->id;
                    $statement->date = $transaction->raw_date;
                    $statement->type = $transaction->type;
                    $statement->category = $transaction->category;
                    $statement->reference = $transaction->reference;
                    $statement->particulars = $transaction->particulars;
                    $statement->quantity = $transaction->quantity;
                    $statement->price = $transaction->price;
                    $statement->debit = $transaction->debit;
                    $statement->credit = $transaction->credit;
                    $statement->balance = $transaction->balance;
                    $statement->state = $transaction->state;
                    $statement->save();
                }
            }
        }
    }

    public static function sendTransactionNotifications($statement, $amount, $action)
    {
        $user = DB::table('users')->find($statement->client_id);
        if (! empty($user)) {
            $recipient = $user->mobile;
            $name = $user->firstname;
            $amount = 'TZS'.number_format($amount);
            $account = $user->flex_acc_no ?? $user->uid;
            $remark = $statement->particulars;
            $datetime = $statement->created_at;
            $balance = 'TZS'.number_format($statement->balance);

            if (strtolower($action) == 'credit') {
                $message = 'Dear '.$name.', '.$amount.' Credited to your account '.$account.' Rmks: '.$remark.' By: '.$datetime.' New Balance:'.$balance.' For any queries contact '.getenv('SUPPORT_NUMBER');
                //                SmsFunctions::send_sms([$user->mobile],$message);
                //                WhatsappMessagesHelper::sendWalletCredited(recipient: $recipient, name: $name, amount: $amount, account: $account, remark: $remark, datetime: $datetime, balance: $balance);
                //                $mailable = new WalletCreditedEmail(name: $name, amount: $amount, account: $account, remark: $remark, datetime: $datetime, balance: $balance);
            } else {
                //                $message  = "Dear ".$name.", ".$amount." Debited form your account ".$account." Rmks: ".$remark." By: ".$datetime." New Balance:".$balance." For any queries contact  ".getenv("SUPPORT_NUMBER");
                //                SmsFunctions::send_sms([$user->mobile],$message);
                //                WhatsappMessagesHelper::sendWalletDebited(recipient: $recipient, name: $name, amount: $amount, account: $account, remark: $remark, datetime: $datetime, balance: $balance);
                //                $mailable = new WalletDebitedEmail(name: $name, amount: $amount, account: $account, remark: $remark, datetime: $datetime, balance: $balance);
            }
            //            \Mail::to($user->email)->queue($mailable);
        }

    }


    private function __prepare_flexcube_posting(Transaction $transaction): void
    {
        FlexcubePrepareFunctions::prepareFlexcubePosting($transaction);
    }
}
