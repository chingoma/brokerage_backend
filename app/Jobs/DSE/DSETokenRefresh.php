<?php

namespace App\Jobs\DSE;

use App\Mail\CustomEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Modules\DSE\Entities\DSESettings;

class DSETokenRefresh implements ShouldQueue
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
                        $settings = DSESettings::first();
                        $endpoint = $settings->base_url."/oauth/token";
                        $payload = json_encode([
                            'username' => $settings->username,
                            'password' => $settings->password,
                            'grant_type' => $settings->grant_type,
                        ]);
                        $response = Http::withHeaders([
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'Authorization' => 'Basic '.$settings->encoded_token,
                        ])
                            ->timeout($settings->timeout)
                            ->withBody($payload)->send('POST', $endpoint)
                            ->onError(function ($error) {
                                $mailable = (new CustomEmail("DSE Token Refresh (failed)"," Token refresh failed. \ln".$error))
                                ->onQueue(getenv("REDIS_QUEUE_PREFIX").'-brokerlink-admin-api-notification-emails');
//                                Mail::to("canwork.job@gmail.com")->queue($mailable);
                            });

                        $body = (object) json_decode($response->body());

                        if (!empty($body->access_token)) {
                            $settings->access_token = $body->access_token;
                            $settings->refresh_token = $body->refresh_token;
                            $settings->expires_in = $body->expires_in;
                            $settings->save();
                            $mailable = (new CustomEmail("DSE Token Refresh (success)"," Token refresh successfully."))
                                ->onQueue(getenv("REDIS_QUEUE_PREFIX").'-brokerlink-admin-api-notification-emails');
                        }else{
                            $mailable = (new CustomEmail("DSE Token Refresh (failed)"," Token refresh failed. \ln".$body))
                                ->onQueue(getenv("REDIS_QUEUE_PREFIX").'-brokerlink-admin-api-notification-emails');
                        }

//            Mail::to("kelvin@lockminds.com")->queue($mailable);

        } catch (\Exception $exception) {
            $mailable = (new CustomEmail('DSE Token Refresh (failed)', " Token refresh failed. \ln".$exception->getMessage()))
                ->onQueue(getenv("REDIS_QUEUE_PREFIX").'-brokerlink-admin-api-notification-emails');
//            Mail::to('kelvin@lockminds.com')->queue($mailable);
        }
    }
}
