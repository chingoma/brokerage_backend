<?php

namespace Modules\Whatsapp\Helpers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Modules\Whatsapp\Data\SendHelloWorldObject;
use Modules\Whatsapp\Data\SendMediaObject;
use Modules\Whatsapp\Data\SendOrderApprovedObject;
use Modules\Whatsapp\Data\SendOrderReceivedMessageObject;
use Modules\Whatsapp\Data\SendOrderReviewedObject;
use Modules\Whatsapp\Data\SendTextMessageObject;
use Modules\Whatsapp\Data\SendTradeConfirmationApprovedObject;
use Modules\Whatsapp\Data\SendTwoFactorMessageObject;
use Modules\Whatsapp\Data\SendWalletCreditedObject;
use Modules\Whatsapp\Data\SendWalletDebitedObject;
use Modules\Whatsapp\Entities\WhatsappSetting;

class WhatsappMessagesHelper
{
    public static function uploadMedia(string $file, string $type): string|object
    {
        try {
            $payload = new SendMediaObject(file: $file, type: $type);

            $endpoint = self::baseUrl().'media';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendWalletCredited(string $recipient, string $name, string $amount, string $account, string $remark, string $datetime, string $balance): string|object
    {
        try {
            $payload = new SendWalletCreditedObject(recipient: $recipient, name: $name, amount: $amount, account: $account, remark: $remark, datetime: $datetime, balance: $balance);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendWalletDebited(string $recipient, string $name, string $amount, string $account, string $remark, string $datetime, string $balance): string|object
    {
        try {
            $payload = new SendWalletDebitedObject(recipient: $recipient, name: $name, amount: $amount, account: $account, remark: $remark, datetime: $datetime, balance: $balance);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendOrderReviewed(string $recipient): string|object
    {
        try {
            $payload = new SendOrderReviewedObject(recipient: $recipient);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendOrderApproved(string $recipient): string|object
    {
        try {
            $payload = new SendOrderApprovedObject(recipient: $recipient);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendTradeConfirmationApprovedObject(string $recipient): string|object
    {
        try {
            $payload = new SendTradeConfirmationApprovedObject(recipient: $recipient);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendOrderReceived(string $name, string $order_type, string $recipient): string|object
    {
        try {
            $payload = new SendOrderReceivedMessageObject(name: $name, order_type: $order_type, recipient: $recipient);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendTwoFactorCode(string $message, string $recipient): string|object
    {
        try {
            $payload = new SendTwoFactorMessageObject(template_name: 'brokerlink_two_factor', message: $message, recipient: $recipient);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendTextMessage(string $message, string $recipient): string|object
    {
        try {
            $payload = new SendTextMessageObject(recipient: $recipient, message: $message);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return (object) json_decode($response->body());

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sendHelloWorld(string $recipient): string|object
    {
        try {
            $payload = new SendHelloWorldObject(recipient: $recipient);

            $endpoint = self::baseUrl().'messages';
            $response = self::myClient()
                ->withBody($payload->message())
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
            .'/'.$settings->whatsapp_id.'/';
    }
}
