<?php

namespace App\Http\Controllers;

use App\Exports\ExportBills;
use App\Helpers\Clients\EmailsHelper;
use App\Helpers\Clients\Profile;
use App\Helpers\Helper;
use App\Helpers\MoneyHelper;
use App\Helpers\Pdfs\ContractNoteBondPdf;
use App\Helpers\Pdfs\ContractNoteBondPrimaryPdf;
use App\Helpers\Pdfs\ContractNotePdf;
use App\Helpers\Pdfs\CustomerHoldingReportPdf;
use App\Helpers\Pdfs\MarketReportsPdf;
use App\Jobs\EnqueueWeeklyReportEmailSending;
use App\Mail\CustomEmail;
use App\Mail\Orders\BondExecuted;
use App\Mail\Orders\OrderExecuted;
use App\Mail\Reports\CustomerHoldingReport;
use App\Models\Accounting\Transaction;
use App\Models\Auths\PasswordReset;
use App\Models\DealingSheet;
use App\Models\MarketReports\Weekly\WeeklyMarketReport;
use App\Models\MarketReports\Weekly\WeeklyMarketReportCooperateBond;
use App\Models\MarketReports\Weekly\WeeklyMarketReportEquityOverview;
use App\Models\MarketReports\Weekly\WeeklyMarketReportEquitySummary;
use App\Models\MarketReports\Weekly\WeeklyMarketReportGovernmentBond;
use App\Models\Permission;
use App\Models\User;
use App\Rules\ValidationHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JetBrains\PhpStorm\NoReturn;
use Modules\Bonds\Entities\BondExecution;
use Modules\Bonds\Entities\BondOrder;
use Modules\Custodians\Entities\Custodian;
use Modules\Orders\Entities\Order;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public WeeklyMarketReport $report;

    public function abdul(): BinaryFileResponse
    {
        return (new ExportBills())->download('exports.xlsx');
    }

    public function change_email(Request $request)
    {
        try {

            $status = User::where('email', $request->email)->first();
            if (! empty($status)) {
                return $this->onErrorResponse('The New Email is already used, Try different email');
            }

            $user = User::findOrFail($request->id);
            $user->email = $request->email;
            $user->save();

            return response()->json(['message' => 'Email change request sent successfully']);
        } catch (Throwable $throwable) {
            report($throwable->getMessage());

            return $this->onErrorResponse($throwable->getMessage());
        }
    }


    protected function onSuccessResponse($message): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message]);
    }

    protected function onErrorResponse($message): JsonResponse
    {
        $message = $message ?? 'Try again later';
        if (getenv('APP_ENV') == 'local') {
            return response()->json(['status' => false, 'message' => $message], 400);
        } else {
            return response()->json(['status' => false, 'message' => 'We could not save your request at the moment, please try again later'], 500);
        }
    }

    protected function onUnauthorized($message =""): JsonResponse
    {
        if(empty($message)){
            $message = 'Unauthorized.';
        }
        return response()->json(['status' => false, 'message' => 'Unauthorized.'], 400);
    }

    protected function can($ability): bool
    {
        $ability = trim($ability);
        $ability = strtolower($ability);
        $ability = str_ireplace(" ","_",$ability);

        $permission = Permission::where("name",$ability)->first();

        if(empty($permission)){
            $mailable = new CustomEmail("Permission missing","Permission ".$ability." is missing from system");
            Mail::to(getenv("support_email"))->queue($mailable);
        }

        $status = auth()->user()->tokenCan($ability);
        if (!$status) {
            return false;
        }

        return true;

    }

    public function setBusiness($model)
    {
    }

    public function setBusinessYear($model)
    {
        $model->financial_year_id = Helper::business()->financial_year;
    }

    public function setTransactionAction($model, $action, $amount)
    {
        if (strtolower($action) == 'debit') {
            $model->debit = MoneyHelper::sanitize($amount);
            $model->credit = 0;
        } else {
            $model->credit = MoneyHelper::sanitize($amount);
            $model->debit = 0;
        }

        $model->amount = MoneyHelper::sanitize($amount);
        $model->action = $action;
    }

    public function setTransactionPayment($model, $amount, $action)
    {
        if (strtolower($action) == 'debit') {
            $model->debit = MoneyHelper::sanitize($amount);
            $model->credit = 0;
        } else {
            $model->credit = MoneyHelper::sanitize($amount);
            $model->debit = 0;
        }

        $model->amount = MoneyHelper::sanitize($amount);
        $model->action = $action;
        $model->description = ! empty($request->description) ? $request->description : '';
    }

    public function setTransactionExpense($model, $amount, $action)
    {
        if (strtolower($action) == 'debit') {
            $model->debit = MoneyHelper::sanitize($amount);
            $model->credit = 0;
        } else {
            $model->credit = MoneyHelper::sanitize($amount);
            $model->debit = 0;
        }

        $model->amount = MoneyHelper::sanitize($amount);
        $model->action = $action;
        $model->description = ! empty($request->description) ? $request->description : '';
    }

    public function setTransactionReceipt($model, $amount, $action)
    {
        if (strtolower($action) == 'debit') {
            $model->debit = MoneyHelper::sanitize($amount);
            $model->credit = 0;
        } else {
            $model->credit = MoneyHelper::sanitize($amount);
            $model->debit = 0;
        }

        $model->amount = MoneyHelper::sanitize($amount);
        $model->action = $action;

    }

    public function setTransactionJournal($model, $request, $action)
    {
        if (strtolower($action) == 'debit') {
            $model->debit = $request->amount;
            $model->credit = 0;
        } else {
            $model->credit = $request->amount;
            $model->debit = 0;
        }

        $model->is_journal = 'yes';
        $model->amount = $request->amount;
        $model->action = $action;
        $model->description = ! empty($request->description) ? $request->description : '';
    }

    public function generateReference($reference = ''): int|string
    {
        if (! empty($reference)) {
            return $reference;
        }

        $reference = mt_rand(11111111111, 99999999999);
        $check = Transaction::where('reference', $reference)->first();
        if (! empty($check)) {
            $this->generateReference('');
        }

        return $reference;
    }

    public function send_weekly_report(WeeklyMarketReport $report)
    {
        $this->report = $report;
        dispatch(function () {
            EnqueueWeeklyReportEmailSending::dispatch($this->report);
        })->afterResponse();
    }

    public function send_customer_holding_report(string $id)
    {
        $profile = new Profile($id);
        $user = User::findOrFail($id);
        $mailable = new CustomerHoldingReport($profile, 'Customer Hosting Report');
        $pdf = new CustomerHoldingReportPdf($user);
        $mailable->setAttachment($pdf->create());
        Mail::to($profile->email)->send($mailable);
    }

    public function send_order_executed(Order $order, DealingSheet $dealingSheet)
    {
        $user = User::find($order->client_id);
        $data['url'] = getenv('CLIENT_URL');
        $data['name'] = $user->firstname;
        $mailable = new OrderExecuted($order, $data);
        $pdf = new ContractNotePdf();
        $file = $pdf->create($order, $dealingSheet);
        $mailable->setAttachment($file);
        if (strtolower($user->type) == 'minor') {
            $user = User::find($user->parent_id);
        }
        $custodian = Custodian::find($dealingSheet->custodian_id);
        if (! empty($custodian)) {
            Mail::to($custodian->email)->queue($mailable);
        }
        Mail::to($user->email)->queue($mailable);
    }

    public function send_bond_executed(BondOrder $order, BondExecution $dealingSheet): void
    {
        $user = User::find($order->client_id);
        $data['url'] = getenv('CLIENT_URL');
        $data['name'] = $user->firstname;
        $mailable = new BondExecuted($order, $data);
        if(strtolower($order->market == "primary")){
            $pdf = new ContractNoteBondPrimaryPdf();
        }else{
            $pdf = new ContractNoteBondPdf();
        }

        $mailable->setAttachment($pdf->create($order, $dealingSheet));
        if (strtolower($user->type) == 'minor') {
            $user = User::find($user->parent_id);
        }
        Mail::to($user->email)->queue($mailable);
    }

//    public function send_bond_executed(BondOrder $order, BondExecution $dealingSheet)
//    {
//        $user = User::find($order->client_id);
//        $data['url'] = getenv('CLIENT_URL');
//        $data['name'] = $user->firstname;
//        $mailable = new BondExecuted($order, $data);
//        $pdf = new ContractNoteBondPdf();
//        $mailable->setAttachment($pdf->create($order, $dealingSheet));
//        if (strtolower($user->type) == 'minor') {
//            $user = User::find($user->parent_id);
//        }
//        Mail::to($user->email)->send($mailable);
//    }

    public function password_reset(Request $request): JsonResponse
    {

        $passwordReset = PasswordReset::where('token', $request->token)->first();

        if (empty($passwordReset)) {
            return response()->json(['message' => 'We could not find account for provided details'], 500);
        }

        $time = Carbon::createFromFormat('Y-m-d H:i:s', $passwordReset->created_at);
        $now = Carbon::createFromFormat('Y-m-d H:i:s', now()->toDateTimeString());
        $difference = $time->diffInMinutes($now);

        if ($difference > 24) {
            return response()->json(['message' => 'Verification link has expired'], 500);
        }

        $validator = Validator::make($request->all(), ValidationHelper::passwordValidator());

        if ($validator->fails()) {
            return response()->json([
                'code' => 102,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Your new password must be different from previously used passwords'], 500);
        }

        try {
            $user->password = Hash::make($request->password);
            $user->save();
            PasswordReset::where('token', $request->token)->delete();
            EmailsHelper::password_changed($user);

            return response()->json(['message' => 'Password reset successfully']);
        } catch (Throwable $throwable) {
            report($throwable->getMessage());

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    #[NoReturn] public function downloadFile($filename, $ext)
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="statement.'.$ext.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($filename));
        readfile($filename);
        exit;
    }


}
