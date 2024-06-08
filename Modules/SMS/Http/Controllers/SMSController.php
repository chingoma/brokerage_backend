<?php

namespace Modules\SMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SMS\Entities\SmsSetting;
use Modules\SMS\Helpers\SmsFunctions;

class SMSController extends Controller
{
    public function settings(Request $request)
    {
        try {
            return response()->json(SmsSetting::first());
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_settings(Request $request)
    {
        try {
            \DB::beginTransaction();
            $setting = SmsSetting::firstOrCreate(['sms_provider' => 'beem']);
            $setting->sms_base_url = $request->sms_base_url;
            $setting->sms_api_key = $request->sms_api_key;
            $setting->sms_secret_key = $request->sms_secret_key;
            $setting->sms_sender_id = $request->sms_sender_id;
            $setting->save();
            if (! empty($request->test_number)) {
                $recipients = explode(',', $request->test_number);
                SmsFunctions::send_sms(recipients: $recipients, message: 'This is sample text message number '.random_int(11111, 99999));
            }
            \DB::commit();

            return response()->json(['status' => true, 'message' => 'Settings updated successfully.']);
        } catch (\Throwable $throwable) {
            \DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }
}
