<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\EmailsHelper;
use App\Http\Controllers\Controller;
use App\Mail\Clients\WelcomeEmailMailable;
use App\Mail\TestEmailDelivery;
use App\Models\Accounting\AccountClass;
use App\Models\Accounting\AccountSetting;
use App\Models\MailingList;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SettingsController extends Controller
{
    public function get_mailing_list(Request $request)
    {
        try {

            return response()->json(MailingList::orderBy('category')->get());

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_mailing_list(Request $request)
    {
        try {

            $data = MailingList::findOrFail($request->id);
            \DB::beginTransaction();
            $data->email = $request->email;
            $data->category = $request->category;
            $data->status = $request->status;
            $data->save();
            \DB::commit();

            return response()->json(MailingList::orderBy('category')->get());

        } catch (Throwable $throwable) {
            \DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function add_mailing_list(Request $request)
    {
        try {

            $status = MailingList::where('email', $request->email)->where('category', $request->category)->first();
            if (! empty($status)) {
                return $this->onErrorResponse('Email exists for category '.ucwords($request->category));
            }

            \DB::beginTransaction();
            $data = new MailingList();
            $data->email = $request->email;
            $data->category = $request->category;
            $data->status = $request->status;
            $data->save();
            \DB::commit();

            return response()->json(MailingList::orderBy('category')->get());

        } catch (Throwable $throwable) {
            \DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function test_email_delivery(Request $request)
    {
        try {

            //            $user = User::where("email","kelvin@brokerlink.co.tz")->first();
            //            $welcomeMailable = new WelcomeEmailMailable($user);
            //            Mail::to($user->email)->send($welcomeMailable);

            $emails = str_replace(' ', ',', $request->emails);
            $emails = explode(',', $emails);

            if (count($emails) < 1) {
                return $this->onErrorResponse('No Recipients');
            }

            if (count($emails) > 5) {
                return $this->onErrorResponse('You can test maximum of 5 Emails per request');
            }

            foreach ($emails as $email) {
                $mailable = new TestEmailDelivery($request->contents);
                \Mail::to($email)->queue($mailable);
            }

            \DB::commit();

            return response()->json('Test pass');

        } catch (Throwable $throwable) {
            \DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function send_predefine_email(Request $request)
    {
        try {

//            if(!$this->can("business settings")){
//                return $this->onErrorResponse("Unauthorized action");
//            }

            $user = User::findOrFail($request->user);

            switch (strtolower($request->category)) {
                case 'overdraft orders':
                    EmailsHelper::sendOverdraftEmail('bond');
                    EmailsHelper::sendOverdraftEmail('equity');
                    break;
                case 'new customer admin':
                    EmailsHelper::newCustomerAdmin($user);
                    break;
                case 'newsletter':
                    EmailsHelper::newsLetter();
                    break;

            }

            return response()->json('Test pass');

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function add_payment_method(Request $request)
    {
        try {

            $data = new PaymentMethod();
            $data->name = $request->name;
            $data->description = $request->description;
            $data->save();

            return $this->settings();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_payment_method(Request $request)
    {
        try {

            $data = PaymentMethod::find($request->id);
            $data->name = $request->name;
            $data->description = $request->description;
            $data->save();

            return $this->settings();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete_payment_method(Request $request)
    {
        try {

            $data = PaymentMethod::find($request->id);
            $data->delete();

            return $this->settings();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function set_order_settings(Request $request)
    {
        try {
            $settings = AccountSetting::first();

            if (empty($settings)) {
                $settings = new AccountSetting();
                $settings->save();
            }

            $settings->order_cash_account = $request->order_cash_account;
            $settings->order_liability_account = $request->order_liability_account;
            $settings->order_revenue_account = $request->order_revenue_account;
            $settings->cmsa_fee_account = $request->cmsa_fee_account;
            $settings->fidelity_fee_account = $request->fidelity_fee_account;
            $settings->dse_fee_account = $request->dse_fee_account;
            $settings->cds_fee_account = $request->cds_fee_account;
            $settings->vat_account = $request->vat_account;
            $settings->custodian_account = $request->custodian_account;

            $settings->cmsa_payee_account = $request->cmsa_payee_account;
            $settings->fidelity_payee_account = $request->fidelity_payee_account;
            $settings->dse_payee_account = $request->dse_payee_account;
            $settings->cds_payee_account = $request->cds_payee_account;
            $settings->vat_payee_account = $request->vat_payee_account;
            $settings->custodian_payee = $request->custodian_payee;

            $settings->save();

            return $this->settings();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function set_customer_settings(Request $request)
    {
        try {
            $settings = AccountSetting::first();
            if (empty($settings)) {
                $settings = new AccountSetting();
                $settings->save();
            }

            $settings->customer_liability_account = $request->customer_liability_account;
            $settings->customer_cash_account = $request->customer_cash_account;

            $settings->save();

            return $this->settings();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function set_receipt_settings(Request $request)
    {
        try {
            $settings = AccountSetting::first();
            if (empty($settings)) {
                $settings = new AccountSetting();
                $settings->save();
            }

            $settings->receipt_expense_account = $request->receipt_expense_account;
            $settings->receipt_cash_account = $request->receipt_cash_account;

            $settings->save();

            return $this->settings();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function set_bill_settings(Request $request)
    {
        try {
            $settings = AccountSetting::first();
            if (empty($settings)) {
                $settings = new AccountSetting();
                $settings->save();
            }

            $settings->bill_liability_account = $request->bill_liability_account;
            $settings->bill_cash_account = $request->bill_cash_account;
            $settings->bill_expense_account = $request->bill_expense_account;

            $settings->save();

            return $this->settings();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settings_data(Request $request)
    {
        try {
            $settings = AccountSetting::first();
            if (empty($settings)) {
                $settings = new AccountSetting();
                $settings->save();
            }

            return $this->settings();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settings()
    {
        try {
            $settings = AccountSetting::first();
            if (empty($settings)) {
                $settings = new AccountSetting();
                $settings->save();
            }

            $response['data'] = AccountSetting::first();
            //            $response['classes'] = AccountClass::all();
            $response['accounts'] = \DB::table('accounts')->select(['name', 'id'])->get();
            //            $response['users'] =  \DB::table("users")->get();
            //            $response['customers'] =  User::customers()->get();
            //            $response['payees'] =  User::payees()->get();
            $response['paymentMethods'] = \DB::table('payment_methods')->select(['name', 'id'])->get();

            return response()->json($response, 200);

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function accounts()
    {
        try {
            $accounts = DB::table('accounts')->select(['name', 'id'])->get();

            return response()->json($accounts, 200);

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function payees()
    {
        try {
            $payees = DB::table('users')->where('type', 'payee')->select(['name', 'id'])->get();

            return response()->json($payees);

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function order_settings()
    {
        try {
            $settings = DB::table('account_settings')->first();

            if (empty($settings)) {
                $settings = new AccountSetting();
                $settings->save();
            }

            return response()->json($settings, 200);

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
