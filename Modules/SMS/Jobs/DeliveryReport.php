<?php

namespace Modules\SMS\Jobs;

use App\Mail\CustomEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Modules\SMS\Entities\Sms;

class DeliveryReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $sms = Sms::whereIn('status', ['submitted', 'pending'])->get();
            if (! empty($sms)) {
                $settings = \Modules\SMS\Entities\SmsSetting::first();
                foreach ($sms as $sm) {
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->withBasicAuth(username: $settings->sms_api_key, password: $settings->sms_secret_key)
                        ->get(
                            'https://dlrapi.beem.africa/public/v1/delivery-reports',
                            [
                                'dest_addr' => $sm->recipient,
                                'request_id' => $sm->request_id,
                            ]
                        )
                        ->onError(function ($error) {
                            $mailable = new CustomEmail('SMS Error', $error);
                            //                            Mail::to("canwork.job@gmail.com")->queue($mailable);
                        });

                    $resp = (object) json_decode($response->body());
                    $gSms = Sms::find($sm->id);
                    if (! empty($resp->error) || ! empty($resp->message)) {
                        $gSms->status = 'failed';
                        $gSms->status = ! empty($resp->error) ? ! $resp->error : $resp->message;
                        $gSms->save();
                    } else {
                        $gSms->status = strtolower($resp->status);
                        $gSms->save();

                        $mailable = new CustomEmail('SMS Delivered', ' SMS sent to '.$gSms->recipient.' was delivered at '.$gSms->updated_at);
                        //                        Mail::to("canwork.job@gmail.com")->queue($mailable);
                    }
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            $mailable = new CustomEmail('SMS Delivery Job (fail)', " failed to complete sms delivery job \n Exception message \n ".$exception->getMessage());
            //            Mail::to("canwork.job@gmail.com")->queue($mailable);
        }
    }
}
