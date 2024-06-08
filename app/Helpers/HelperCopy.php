<?php

// Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Data\PusherEventData;
use App\Events\SendNotification;
use App\Models\Accounting\Account;
use App\Models\Accounting\FinancialYear;
use App\Models\Accounting\Transaction;
use App\Models\Business;
use App\Models\DealingSheet;
use App\Models\DealingSheetId;
use App\Models\MailingList;
use App\Models\OrderId;
use App\Models\Profile;
use App\Models\TransactionId;
use App\Models\User;
use App\Models\UserId;
use Illuminate\Support\Carbon;
use Modules\Assets\Entities\AssetId;
use Modules\Assets\Entities\Assets;
use Modules\Calendar\Entities\Calendar;
use Modules\Orders\Entities\Order;

class HelperCopy
{
    public static function pdfSignature($pdf, $barcode = 'lockminds'): void
    {
        $pdf->SetCreator('Lockminds Brokerage Software (BrokerLink)');
        $pdf->SetAuthor(getenv('AUTHOR'));
        $pdf->SetTitle('Customer Cash Statement');
        $pdf->SetKeywords(getenv('KEYWORDS'));
        $pdf->setBarcode($barcode);
        $pdf->setSubject('Customer Reports');
    }

    public static function pdfProtection($pdf, $user_pass = '', $owner_pass = ''): void
    {
        $owner_pass = $owner_pass == '' ? getenv('PDF_OWNER_PASSWORD') : $owner_pass;
        $user_pass = $user_pass == '' ? getenv('PDF_USER_PASSWORD') : $user_pass;
        $mode = 0;
        //        $permissions = array('open','print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');
        $permissions = ['open', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'];
        $pdf->SetProtection($permissions, $user_pass, $owner_pass, $mode, $pubkeys = null);
    }

    public static function action_response_emails(): array
    {
        return ['kelvin@lockminds.co.tz'];
    }

    public static function customerCompanyShares($id, $cid)
    {
        $buy = \DB::table('dealing_sheets')->where('client_id', $cid)->where('status', 'approved')->where('security_id', $id)->where('type', 'buy')->sum('executed');
        $sell = \DB::table('dealing_sheets')->where('client_id', $cid)->where('status', 'approved')->where('security_id', $id)->where('type', 'sell')->sum('executed');

        //        $sell = \DB::table("orders")
        //            ->where("client_id",$cid)
        //            ->where("status","!=","cancelled")
        //            ->where("security_id",$id)
        //            ->where("type","sell")
        //            ->sum("volume");
        return $buy - $sell;
    }

    public static function customerBondFaceValue($id, $cid)
    {
        $buy = \DB::table('bond_executions')->where('client_id', $cid)->where('status', 'approved')->where('bond_id', $id)->where('type', 'buy')->sum('executed');
        $sell = \DB::table('bond_executions')->where('client_id', $cid)->where('status', 'approved')->where('bond_id', $id)->where('type', 'sell')->sum('executed');

        return $buy - $sell;
    }

    public static function mailingListOverdraft(): array
    {
        return MailingList::where('category', 'overdraft orders')->get()->toArray();
    }

    public static function setEnv($envKey, $envValue)
    {
        $path = app()->environmentFilePath();
        $escaped = preg_quote('='.env($envKey), '/');
        //update value of existing key
        file_put_contents($path, preg_replace(
            "/^{$envKey}{$escaped}/m",
            "{$envKey}={$envValue}",
            file_get_contents($path)
        ));
        //if key not exist append key=value to end of file
        $fp = fopen($path, 'r');
        $content = fread($fp, filesize($path));
        fclose($fp);
        if (strpos($content, $envKey.'='.$envValue) == false && strpos($content, $envKey.'='.'\"'.$envValue.'\"') == false) {
            file_put_contents($path, $content."\n".$envKey.'='.$envValue);
        }
    }

    public static function getTimestamp()
    {
        $timezone = getenv('TIMEZONE');

        return now($timezone)->toDateTimeString();
    }

    public static function formattedDate($date): string
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    public static function settings()
    {
        return Business::first();
    }

    public static function backupLocation($path = '')
    {
        return dirname(base_path()).'/main_storage/backups/';
    }

    public static function storageArea($path = '')
    {
        if (! empty($path)) {
            return dirname(base_path()).'/main_storage/'.$path.'/';
        } else {
            return dirname(base_path()).'/main_storage/';
        }
    }

    public static function profile($id = null)
    {
        if (! empty($id)) {
            return User::find($id);
        } else {
            return Profile::where('user_id', \auth()->user()->id)->first();
        }
    }

    public static function environment(): string
    {
        return (request()->getScheme() == 'http') ? 'local' : 'production';
    }

    public static function number($number): array|string
    {
        return str_replace(',', '', $number);
    }

    public static function customerTypes(): array
    {
        return ['individual', 'cooperate', 'joint'];
    }

    public static function employmentStatus(): array
    {
        return ['employed', 'self employed', 'retired', 'other'];
    }

    public static function cooperateTypes(): array
    {
        return ['company', 'trust', 'others'];
    }

    public static function business()
    {
        return Business::first();
    }

    public static function makeSmsReceiver($number, $code = 255): array|string
    {

        $number = str_replace('+', '', $number);
        $output = $number;
        if ($number[0] == 0) {
            $output = $code.ltrim($number, $number[0]);
        }

        return $output;
    }

    public static function orderStatusChanged(Order $order): void
    {
        $event = new PusherEventData();
        $event->targets = $order->client_id;
        $event->channel = 'notification';
        $event->event = 'notifications';
        $event->title = 'Order status changed';
        $event->message = 'Order status changed to '.$order->status;
        //   event( new SendNotification($event));
    }

    public static function orderCreated(Order $order)
    {
        $event = new PusherEventData();
        $event->source = $order->client_id;
        $event->channel = 'notification';
        $event->event = 'notifications';
        $event->title = 'New Order created from client';
        $event->message = 'Client  '.$order->client->name.' created new order';
        event(new SendNotification($event));
    }

    public static function account($account)
    {
        return Account::find($account);
    }

    public static function accountByName($account)
    {
        return Account::whereName($account)->first();
    }

    public static function generateRandomString($length = 5): string
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    public static function customerUID(User $model): string
    {

        $id = '';

        $status = UserId::withTrashed()->latest('lap')->limit(1)->first();

        if (empty($status)) {
            $nextLap = 200000;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = UserId::withTrashed()->where('lap', $nextLap)->latest('lap')->limit(1)->first();

        if (empty($nextStatus)) {
            $id = $id.$nextLap;
            $data = new UserId();
            $data->uid = $id;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->save();
        } else {
            self::customerUID($model);
        }

        return $id;
    }

    public static function orderUID(Order $model): string
    {

        $systemDate = HelperCopy::systemDateTime();
        $year = date('Y', strtotime($systemDate['today']));

        $id = 'ITFL/'.$year.'/';

        $status = OrderId::withTrashed()
            ->where('year', $year)
            ->latest('lap')
            ->limit(1)
            ->first();

        if (empty($status)) {
            $nextLap = 1;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = OrderId::withTrashed()
            ->where('year', $year)
            ->where('lap', $nextLap)
            ->latest()
            ->limit(1)
            ->first();

        if (empty($nextStatus)) {
            $id = $id.$nextLap;
            $data = new OrderId();
            $data->year = $year;
            $data->uid = $id;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->save();
        } else {
            self::orderUID($model);
        }

        return $id;
    }

    public static function dealingSheetUID(DealingSheet $model): string
    {
        $systemDate = HelperCopy::systemDateTime();
        $year = date('Y', strtotime($systemDate['today']));
        $day = date('d', strtotime($systemDate['today']));
        $month = date('m', strtotime($systemDate['today']));

        $tp = (strtolower($model->type) == 'buy') ? 'P' : 'S';
        $id = 'ITFL/'.$year.'/';
        $ref = 'EQ'.$tp.$day.$month.$year.'/';

        $status = DealingSheetId::withTrashed()
            ->where('year', $year)
            ->latest('lap')
            ->limit(1)
            ->first();

        if (empty($status)) {
            $nextLap = 1;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = DealingSheetId::withTrashed()
            ->where('year', $year)
            ->where('lap', $nextLap)
            ->latest()
            ->limit(1)
            ->first();

        if (empty($nextStatus)) {
            $id = $id.$nextLap;
            $data = new DealingSheetId();
            $data->year = $year;
            $data->uid = $id;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->reference = $ref.$nextLap;
            $model->save();
        } else {
            self::dealingSheetUID($model);
        }

        return $id;
    }

    public static function transactionUID(Transaction $model): string
    {

        $systemDate = HelperCopy::systemDateTime();
        $year = date('Y', strtotime($systemDate['today']));

        $id = 'ITFLT/'.$year.'/';

        $status = TransactionId::withTrashed()
            ->where('year', $year)
            ->latest('lap')
            ->limit(1)
            ->first();

        if (empty($status)) {
            $nextLap = 1;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = TransactionId::withTrashed()
            ->where('year', $year)
            ->where('lap', $nextLap)
            ->latest()
            ->limit(1)
            ->first();

        if (empty($nextStatus)) {
            $id = $id.$nextLap;
            $data = new TransactionId();
            $data->uid = $id;
            $data->year = $year;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->save();
        } else {
            self::transactionUID($model);
        }

        return $id;
    }

    public static function assetUID(Assets $model, $id): string
    {

        $status = AssetId::withTrashed()->latest('created_at')->limit(1)->first();

        if (empty($status)) {
            $nextLap = 1;
        } else {
            $nextLap = $status->lap + 1;
        }

        $nextStatus = AssetId::withTrashed()->where('lap', $nextLap)->latest('created_at')->limit(1)->first();

        if (empty($nextStatus)) {
            $id = $id.'-'.$nextLap;
            $data = new AssetId();
            $data->uid = $id;
            $data->lap = $nextLap;
            $data->foreign_id = $model->id;
            $data->save();
            $model->uid = $id;
            $model->save();
        } else {
            self::assetUID($model, $id);
        }

        return $id;
    }

    public static function corporateTypes(): array
    {
        return ['Company', 'Trust', 'Fund', 'Other'];
    }

    public static function financialYear(): FinancialYear
    {
        return FinancialYear::where('status', 'active')->first();
    }

    public static function systemDateTime($dateInput = null): array
    {
        if (empty($dateInput)) {
            $today = now(getenv('TIMEZONE'))->toDateString();
        } else {
            $today = date('Y-m-d', strtotime($dateInput));
        }

        $status = Calendar::groupBy('today')
            ->where('closed', false)
            ->where('calendar', 'Business')
            ->limit(1)
            ->orderBy('today')
            ->first();

        if (! empty($status)) {
            $date = $status->today;
        } else {
            $date = $today;
        }

        $newDate = date(' l M, d Y', strtotime($date));
        $response['today'] = $date;
        $response['formatted'] = $newDate;
        $response['timely'] = Carbon::createFromFormat('Y-m-d', $date)->toDateTimeString();

        return $response;
    }

    public static function settlementDateEquity($date = ''): string
    {
        $today = date('Y-m-d', strtotime($date));
        $status = Calendar::groupBy('today')
           // ->where("closed",false)
            ->where('calendar', 'Business')
            ->whereDate('today', '>', $today)
            ->limit(3)->orderBy('today')
            ->pluck('today');
        if (! empty($status)) {
            $index = count($status) - 1;
            $settlementDate = $status[$index];
        } else {
            $settlementDate = $today;
        }

        return $settlementDate;
    }

    public static function settlementDateBond($date = ''): string
    {
        $today = date('Y-m-d', strtotime($date));
        $status = Calendar::groupBy('today')
           // ->where("closed",false)
            ->where('calendar', 'Business')
            ->whereDate('today', '>', $today)
            ->limit(1)->orderBy('today')
            ->pluck('today');
        if (! empty($status)) {
            $settlementDate = $status[0];
        } else {
            $settlementDate = $today;
        }

        return $settlementDate;
    }
}
