<?php

use App\Data\PusherEventData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Modules\DealingSheets\Http\Controllers\DealingSheetsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
//


Route::get('fix', [\Modules\Bonds\Http\Controllers\BondExecutionsController::class,'approveFix']);

Route::get("get-files", function (Request $request) {
    return Storage::disk('main_storage')->get($request->path.'/'.$request->file);
});


Route::get('/bond', function () {
    $bond = \Modules\Bonds\Entities\BondExecution::find("018e8019-7c30-725d-88e5-17be6d347d54");
    $bond->type = "buy";
    $bond->save();
});

Route::get('info', function () {
//    ini_set('memory_limit', '4095M');
    echo phpinfo();
});


Route::get('check-env', function () {

  if(getenv("CHECK_ENV") == "lockminds-environment"){
      echo "working";
  }else{
      echo "Application was unable to read environment file, Probably could be issue with file Permission";
  }
});

Route::get('event', function () {
//    date_default_timezone_set('Europe/Riga');
//    echo phpinfo();
//
//    echo "start"."<br/>";
//    echo "get env".getenv("TIMEZONE")."<br/>";
//    echo "env".env("TIMEZONE")."<br/>";
//    echo "end"."<br/>";

    $event = new PusherEventData();
    $event->source = '';
    $event->channel = 'data-stream';
    $event->event = 'data-stream';
    $event->title = 'data-strean';
    $event->message = 'data-stream';
    dd(event(new \App\Events\DataStreamEvent($event)));
});

Route::get('events', function () {

   // echo phpinfo();

    echo "start"."<br/>";
    echo "get env".getenv("APP_NAME")."<br/>";
    echo "env".env("APP_NAME")."<br/>";
    echo "end"."<br/>";

//    $event = new PusherEventData();
//    $event->source = '';
//    $event->channel = 'data-stream';
//    $event->event = 'data-stream';
//    $event->title = 'data-strean';
//    $event->message = 'data-stream';
//    dd(event(new \App\Events\DataStreamEvent($event)));
});
//
//Route::get('sync-payments', function () {
//
//    $transactions = Transaction::groupBy('reference')
//        ->whereNull('is_journal')
//        ->latest('transaction_date')
//        ->where('category', 'payment')
//        ->get();
//
//    if (! empty($transactions)) {
//        DB::table('payments')->truncate();
//        foreach ($transactions as $transaction) {
//            $status = \DB::table('payments')
//                ->select('id')
//                ->where('trans_id', $transaction->id)
//                ->first();
//            if (empty($status)) {
//                $receipt = new \Modules\Payments\Entities\Payment();
//                $receipt->trans_id = $transaction->id;
//                $receipt->reference = $transaction->reference;
//                $receipt->client_id = $transaction->client_id;
//                $receipt->date = $transaction->transaction_date;
//                $receipt->particulars = $transaction->title;
//                $receipt->amount = $transaction->amount;
//                $receipt->uid = $transaction->uid;
//                $receipt->status = $transaction->status;
//                $receipt->save();
//            }
//        }
//    }
//});
//
//Route::get('sync-receipts', function () {
//    $transactions = Transaction::groupBy('reference')
//        ->whereNull('is_journal')
//        ->latest('transaction_date')
//        ->where('category', 'receipt')
//        ->get();
//
//    if (! empty($transactions)) {
//        DB::table('receipts')->truncate();
//        foreach ($transactions as $transaction) {
//            $status = \DB::table('receipts')
//                ->select('id')
//                ->where('trans_id', $transaction->id)
//                ->first();
//            if (empty($status)) {
//                $receipt = new Receipt();
//                $receipt->trans_id = $transaction->id;
//                $receipt->reference = $transaction->reference;
//                $receipt->client_id = $transaction->client_id;
//                $receipt->date = $transaction->transaction_date;
//                $receipt->particulars = $transaction->title;
//                $receipt->amount = $transaction->amount;
//                $receipt->uid = $transaction->uid;
//                $receipt->status = $transaction->status;
//                $receipt->save();
//            }
//        }
//    }
//});
//
//Route::get('sync-users', function () {
//
//    try {
//        DB::beginTransaction();
////        syncUsers();
//        DB::commit();
//        $response = User::latest()->limit(10)->get();
//
//        return response()->json($response);
//    } catch (Exception $exception) {
//        DB::rollBack();
//        var_dump($exception->getMessage());
//    }
//
//});
//
//Route::get('sync-orders', function () {
//
//    try {
//        DB::table('orders')->truncate();
//        DB::table('securities')->truncate();
//
//        $orders = DB::connection('alphacap')->table('orders')->get();
//
//        foreach ($orders as $item) {
//            DB::table('orders')
//                ->insert([
//                    'id' => $item->id,
//                    'uid' => $item->uid,
//                    'created_at' => $item->created_at,
//                    'updated_at' => $item->updated_at,
//                    'deleted_at' => $item->deleted_at,
//                    'status' => $item->status]);
//
//            $order = Order::withTrashed()->findOrFail($item->id);
//            if (! empty($order)) {
//                $user = User::withTrashed()->findOrFail($item->client_id);
//                $order->has_custodian = 'no';
//                $order->brokerage_rate = '';
//                $order->client_id = $item->client_id;
//                $order->type = $item->type;
//                $order->date = $item->date;
//                $order->price = $item->price;
//                $order->volume = $item->volume;
//                $order->amount = $order->price * $order->volume;
//                $order->security_id = $item->security_id;
//                $order->mode = $item->price == 0 ? 'limit' : 'market';
//                $order->financial_year_id = $item->financial_year_id;
//                $order->save();
//            }
//        }
//
//        $securities = DB::connection('alphacap')->table('securities')->get();
//
//        foreach ($securities as $item) {
//            DB::table('securities')
//                ->insert([
//                    'id' => $item->id,
//                    'created_at' => $item->created_at,
//                    'updated_at' => $item->updated_at,
//                    'deleted_at' => $item->deleted_at]);
//
//            $order = \App\Models\Security::find($item->id);
//            if (! empty($order)) {
//                $order->name = $item->name;
//                $order->save();
//            }
//        }
//
//        $response = Order::latest()->limit(10)->get();
//
//        return response()->json($response);
//    } catch (Throwable $exception) {
//        report($exception);
//        var_dump($exception->getMessage().' ===== '.$exception->getLine());
//    }
//
//});
//
//Route::get('sync-notes', function () {
//
//    //    DB::beginTransaction();
//    try {
//        DB::table('dealing_sheets')->truncate();
//        $orders = DB::connection('alphacap')->table('dealing_sheets')->get();
//
//        foreach ($orders as $item) {
//            DB::table('dealing_sheets')
//                ->insert([
//                    'id' => $item->id,
//                    'uid' => $item->uid,
//                    'reference' => $item->uid ?? '',
//                    'created_at' => $item->created_at,
//                    'updated_at' => $item->updated_at,
//                    'deleted_at' => $item->deleted_at,
//                    'status' => $item->status]);
//
//            $order = DealingSheet::find($item->id);
//            $orderData = Order::find($item->order_id);
//            $order->trade_date = $item->trade_date;
//            $order->settlement_date = $item->settlement_date;
//            $order->volume = $orderData->volume ?? 0;
//            $order->other_charges = 0;
//            $order->slip_no = $item->slip_no;
//            $order->balance = $item->balance;
//            $order->price = $item->price;
//            $order->amount = $item->amount;
//            $order->executed = $item->executed;
//            $order->mode = $item->price == 0 ? 'limit' : 'market';
//            $order->type = $item->type;
//            $order->order_id = $item->order_id;
//            $order->client_id = $item->client_id;
//            $order->security_id = $item->security_id;
//            $order->financial_year_id = $item->financial_year_id;
//            $order->brokerage_rate = '';
//            $order->vat = $item->vat;
//            $order->brokerage = $item->brokerage;
//            $order->total_commissions = $item->total_commissions;
//            $order->cmsa = $item->cmsa;
//            $order->dse = $item->dse;
//            $order->closed = $item->closed;
//            $order->fidelity = $item->fidelity;
//            $order->total_fees = $item->total_fees;
//            $order->cds = $item->cds;
//            $order->commission_step_one = $item->commission_step_one;
//            $order->commission_step_two = $item->commission_step_two;
//            $order->commission_step_three = $item->commission_step_three;
//            $order->payout = $item->payout;
//            $order->save();
//        }
//
//        //        DB::commit();
//        $response = DealingSheet::latest()->limit(1)->get();
//
//        return response()->json($response);
//    } catch (Exception $exception) {
//        //        DB::rollBack();
//        return response()->json($exception->getMessage());
//    }
//
//});
//
//Route::get('sync-transactions', function () {
//    try {
//        syncTransactions();
//        $response = \App\Models\Accounting\Transaction::latest()->limit(2)->get();
//
//        return response()->json($response);
//    } catch (Exception $exception) {
//    }
//
//});
//
////function syncUsers(): void
////{
////
////    User::whereNotIn('email', ['kelvin@brokerlink.co.tz', 'info@brokerlink.co.tz'])->forceDelete();
////
////    Profile::whereNotIn('email', ['kelvin@brokerlink.co.tz', 'info@brokerlink.co.tz'])->forceDelete();
////
////    $users = DB::connection('alphacap')
////        ->table('users')
////        ->select(['users.*', 'profiles.*'])
////        ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
////        ->selectRaw('profiles.name as names')
////        ->selectRaw('profiles.uid as uids')
////        ->selectRaw('users.id as ids')
//////        ->limit(2)
////        ->get();
////    //   dd($users);
////
////    foreach ($users as $item) {
////        $firstname = '';
////        $middlename = '';
////        $lastname = '';
////        $name = '';
////        $category = CustomerCategory::first();
////        DB::table('users')
////            ->insert([
////                'id' => $item->user_id,
////                'email' => $item->email,
////                'created_at' => $item->created_at,
////                'updated_at' => $item->updated_at,
////                'deleted_at' => $item->deleted_at, ]);
////        $user = User::withTrashed()->where('email', $item->email)->first();
////        $parts = explode(' ', $item->names);
////        $firstname = $parts[0];
////        if (count($parts) > 1) {
////            if (count($parts) == 2) {
////                $lastname = $parts[1];
////                $name = $firstname.' '.$lastname;
////            } else {
////                $middlename = $parts[1];
////                $lastname = $parts[2];
////                $name = $firstname.' '.$middlename.' '.$lastname;
////            }
////        } else {
////            $name = $firstname;
////        }
////
////        $isSpam = ! empty($parts[1]) ? 'no' : 'yes';
////        $user->setDynamic('spam', $isSpam);
////        $user->name = $name;
////        $user->firstname = $firstname;
////        $user->middlename = $middlename;
////        $user->lastname = $lastname;
////        $user->risk_status = '';
////        $user->flex_acc_no = '';
////
////        $user->email = $item->email;
////        $user->mobile = $item->contact_telephone;
////        $user->status = 'pending';
////        $user->type = 'individual';
////        $user->email_verified_at = now()->toDateTimeString();
////        $user->password = Hash::make('123456789');
////        $user->self_registration = false;
////
////        $user->setDynamic('bot_account', $item->bot_account);
////        $user->dse_account = $item->dse_account;
////        $user->setDynamic('bank_name', $item->bank);
////        $user->bank_account_name = $name;
////        $user->bank_account_number = $item->bank_account;
////        $user->bank_branch = '';
////        $user->manager_id = $category->manager_id;
////        $user->category_id = $category->id;
////        $user->is_admin = false;
////        $user->uid = $item->uids;
////        $user->save();
////
////        $user->has_custodian = 'no';
////        $user->custodian_approved = 'no';
////        $user->save();
////        $country = \Nnjeim\World\Models\Country::where('iso2', $item->country_iso)->first();
////        $profile = new Profile();
////        $profile->title = '';
////        $profile->name = $name;
////        $profile->firstname = $firstname;
////        $profile->middlename = $middlename;
////        $profile->lastname = $lastname;
////        $profile->gender = $item->gender;
////        $profile->dob = $item->dob;
////        if (! empty($item->identity)) {
////            $profile->identity = $item->identity;
////        }
////        $profile->country_id = $country->id ?? '';
////        $profile->nationality = ($item->country_iso == 'TZ') ? 'Tanzanian' : '';
////        $profile->address = $item->address;
////        $profile->mobile = $item->contact_telephone;
////        $profile->email = $item->email;
////        $profile->user_id = $user->id;
////        $profile->save();
////
////    }
////}
//
//function syncOrders(): void
//{
//    DB::table('orders')->delete();
//    $orders = DB::connection('alphacap')->table('orders')->get();
//
//    foreach ($orders as $item) {
//        DB::table('orders')
//            ->insert([
//                'id' => $item->id,
//                'uid' => $item->uid,
//                'created_at' => $item->created_at,
//                'updated_at' => $item->updated_at,
//                'deleted_at' => $item->deleted_at,
//                'status' => $item->status]);
//
//        $order = DB::table('orders')->find($item->id);
//        $order->has_custodian = 'no';
//        $order->brokerage_rate = '';
//        $order->client_id = $item->client_id;
//        $order->type = $item->type;
//        $order->date = $item->date;
//        $order->price = $item->price;
//        $order->volume = $item->volume;
//        $order->amount = $order->price * $order->volume;
//        $order->security_id = $item->security_id;
//        $order->mode = $item->price == 0 ? 'limit' : 'market';
//        $order->financial_year_id = $item->financial_year_id;
//        $order->save();
//    }
//}
//
//function syncNotes(): void
//{
//    $orders = DB::connection('alphacap')->table('dealing_sheets')->get();
//    DB::table('dealing_sheets')->truncate();
//    foreach ($orders as $item) {
//        DB::table('dealing_sheets')
//            ->insert([
//                'id' => $item->id,
//                'uid' => $item->uid,
//                'reference' => $item->uid ?? '',
//                'created_at' => $item->created_at,
//                'updated_at' => $item->updated_at,
//                'deleted_at' => $item->deleted_at,
//                'status' => $item->status]);
//
//        $order = DealingSheet::withTrashed()->find($item->id);
//        $orderData = Order::find($item->order_id);
//        $order->trade_date = $item->trade_date;
//        $order->settlement_date = $item->settlement_date;
//        $order->volume = $orderData->volume ?? 0;
//        $order->other_charges = 0;
//        $order->slip_no = $item->slip_no;
//        $order->balance = $item->balance;
//        $order->price = $item->price;
//        $order->amount = $item->amount;
//        $order->executed = $item->executed;
//        $order->mode = $item->price == 0 ? 'limit' : 'market';
//        $order->type = $item->type;
//        $order->order_id = $item->order_id;
//        $order->client_id = $item->client_id;
//        $order->security_id = $item->security_id;
//        $order->financial_year_id = $item->financial_year_id;
//        $order->brokerage_rate = '';
//        $order->vat = $item->vat;
//        $order->brokerage = $item->brokerage;
//        $order->total_commissions = $item->total_commissions;
//        $order->cmsa = $item->cmsa;
//        $order->dse = $item->dse;
//        $order->closed = $item->closed;
//        $order->fidelity = $item->fidelity;
//        $order->total_fees = $item->total_fees;
//        $order->cds = $item->cds;
//        $order->commission_step_one = $item->commission_step_one;
//        $order->commission_step_two = $item->commission_step_two;
//        $order->commission_step_three = $item->commission_step_three;
//        $order->payout = $item->payout;
//        $order->save();
//    }
//}
//
//function syncTransactions(): void
//{
//    $orders = DB::connection('alphacap')->table('transactions')->get();
//    //    DB::table('transactions')->truncate();
//    foreach ($orders as $item) {
//        $status = DB::table('transactions')->find($item->id);
//        if (empty($status)) {
//            DB::table('transactions')
//                ->insert([
//                    'id' => $item->id,
//                    'uid' => $item->uid,
//                    'created_at' => $item->created_at,
//                    'updated_at' => $item->updated_at,
//                    'deleted_at' => $item->deleted_at,
//                    'status' => $item->status]);
//
//            $order = \App\Models\Accounting\Transaction::withTrashed()->find($item->id);
//
//            $order->title = $item->title;
//            $order->amount = $item->amount;
//            $order->cheque_number = $item->cheque_number;
//            $order->withdraw_account = $item->withdraw_account;
//            $order->cash_account = $item->cash_account;
//            $order->payment_type = $item->payment_type;
//            $order->expense_type = $item->expense_type;
//            $order->receipt_type = $item->receipt_type;
//            $order->transaction_date = $item->transaction_date;
//            $order->debit = $item->debit;
//            $order->credit = $item->credit;
//            $order->reference = $item->reference;
//            $order->slip_number = $item->slip_number;
//            $order->category = $item->category;
//            $order->action = $item->action;
//            $order->description = $item->description;
//            $order->is_journal = $item->is_journal;
//            $order->status = $item->status;
//            $order->customer_action = $item->customer_action;
//            $order->vat_type = $item->vat_type;
//            $order->updated_at = $item->updated_at;
//            $order->deleted_at = $item->deleted_at;
//            $order->account_category_id = $item->account_category_id;
//            $order->account_id = $item->account_id;
//            $order->class_id = $item->class_id;
//            $order->client_id = $item->client_id;
//            $order->financial_year_id = $item->financial_year_id;
//            $order->order_id = $item->order_id;
//            $order->payment_method_id = $item->payment_method_id;
//            $order->reconciled = $item->reconciled;
//            $order->real_account_id = $item->real_account_id;
//            $order->uid = $item->uid;
//            $order->external_reference = $item->external_reference;
//            $order->reject_resoan = $item->reject_resoan;
//
//            $order->save();
//        }
//    }
//}
//
//Route::get('health', \Spatie\Health\Http\Controllers\HealthCheckResultsController::class);
//
//Route::get('download-contract-note', [BondExecutionsController::class, 'downloadContractNote']);
//
//Route::get('push', function () {
//
//    $event = new PusherEventData();
//    $event->targets = 0;
//    $event->channel = 'permissions';
//    $event->event = 'permissions';
//    $event->title = '';
//    $event->message = '';
//    event(new PermissionsNotification($event));
//
//});
//
//Route::get('security-create', function () {
//    $data = [
//        'prefix' => 'itrust',
//        'name' => 'iTrust',
//        'domain' => 'www.itrust.co.tz',
//        'licensed' => 'itrust.co.tz',
//    ];
//
//    return \App\Helpers\Security::createSignature($data);
//});
//
//Route::get('security-verify', function () {
//    $data = [
//        'prefix' => 'itrust',
//        'name' => 'iTrust',
//        'domain' => 'www.itrust.co.tz',
//        'licensed' => 'itrust.co.tz',
//    ];
//
//    $key = '-----BEGIN PUBLIC KEY----- MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0Zoh0AddVd0bcFS5V6kk KcPK2+lS3wEjJDFu7XwyiIRMzO1YObFNQ5/HaeWEt6AvMCEpj1+WDuU+JZCzuq9c MZgh8ExHW5SoCoK4+wnqSoZhBcjSSzu9ZCxSSpV2s1sIQ0GgulBrz6kjRuHFUeGW ZiIkqUeTnR59e0nkXSviVdloiSyoBQ1cK18uoDeFbXGuQl9fl2HLSLsUNg9gzdDi Bj+ylcrq5ivhorSE4J5cGW+3F/8Mzar6Mds+muKS0xQmnxMXnioc0t/Lsij96wpn VUxqox/YCgiMS9V6PJ/JQuEB/5/Udf5H5ROd/OtjFBplsVks8mvOZE3PSeB1acZO mQIDAQAB -----END PUBLIC KEY----- ';
//
//    return \App\Helpers\Security::verifySignature($data, $key);
//});
//
//Route::get('php', function () {
//    $data = 'my data';
//
//    //create new private and public key
//    //    $new_key_pair = openssl_pkey_new(array(
//    //        "private_key_bits" => 2048,
//    //        "private_key_type" => OPENSSL_KEYTYPE_RSA,
//    //    ));
//    //    openssl_pkey_export($new_key_pair, $private_key_pem);
//
//    //    $details = openssl_pkey_get_details($new_key_pair);
//    //    $public_key_pem = $details['key'];
//
//    //create signature
//    //    openssl_sign($data, $signature, $private_key_pem, OPENSSL_ALGO_SHA256);
//
//    //save for later
//    //    file_put_contents(\App\Helpers\Helper::storageArea("signature").'private_key.pem', $private_key_pem);
//    //    file_put_contents(\App\Helpers\Helper::storageArea("signature").'public_key.pem', $public_key_pem);
//    //    file_put_contents(\App\Helpers\Helper::storageArea("signature").'signature.dat', $signature);
//
//    //verify signature
//    $public_key_pem = '-----BEGIN PUBLIC KEY-----
//        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuwIMAN7Kx69L6c8gejW5
//        2lN4ULLpDtEOKg3xiTrBmp6npDz28fcPAgzDse97yXoWaYnXB7VC1uj93dLKdVq7
//        ZtIh5+/PwO4mzXonjPTwqBcP0kqWIcQigq/w1jvnTxqaELdC6W4JJYz60fzhdBS6
//        ZVQma98eeO6w6sVnv4lHd7DS324pBrVSsibTu2MkPGRXCSwtKg9mQiY98f+EwJ4e
//        1MPERG7LD93/7As+IAKgNHKczHXRC7OTVUeevnhjs3p5+owZ8SKpIGEjYbaCPAok
//        TToo1nynYRhNsj29UZ+j0LEe56ICHzv+7kcoGByYFIPQyr80r8DOODzzF1gk3ODb
//        4wIDAQAB
//-----END PUBLIC KEY-----';
//    $signature = file_get_contents(\App\Helpers\Helper::storageArea('signature').'signature.dat');
//    $r = openssl_verify($data, $signature, $public_key_pem, 'sha256WithRSAEncryption');
//    var_dump($r);
//});
//
//Route::middleware(['auth:sanctum', 'isAdmin', config('jetstream.auth_session'), 'verified'])->group(function () {
//
//    Route::get('/dashboard', function () {
//        return view('dashboard');
//    })->name('dashboard');
//
//    Route::get('/', function () {
//        return view('dashboard');
//    })->name('dashboard');
//
//});
