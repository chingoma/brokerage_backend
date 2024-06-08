<?php

namespace Modules\Whatsapp\Helpers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Modules\Whatsapp\Entities\WhatsappSetting;

class WhatsappHelper
{
    public static function subscribeWebhook(): string|object
    {
        try {

            $base_url = self::baseUrl();
            $endpoint = $base_url.'/subscribed_apps';
            $response = self::myClient()
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    private static function myClient(): PendingRequest
    {
        $settings = WhatsappSetting::first();

        return Http::withHeaders(['Authorization' => 'Bearer '.$settings->access_token])
            ->acceptJson()
            ->contentType('application/json');
    }

    private static function baseUrl(): string
    {
        $settings = WhatsappSetting::first();

        return $settings->base_url.
            '/'.$settings->api_version
            .'/'.$settings->w_whatsapp_id;
    }
}
