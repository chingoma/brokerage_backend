<?php

namespace Modules\SMS\Helpers;

use Illuminate\Support\Facades\Http;
use Modules\SMS\Entities\SmsSetting;
use Throwable;

class SmsFunctions
{
    public static function send_sms(array $recipients, string $message): \Illuminate\Http\JsonResponse
    {
        try {
            $conversation_id = self::sms_conversation_id();
            $settings = SmsSetting::first();
            $destinations = [];
            foreach ($recipients as $key => $recipient) {
                $id = $key + 1;
                $sms = new \Modules\SMS\Entities\Sms();
                $sms->conversation_id = $conversation_id;
                $sms->recipient = $recipient;
                $sms->message = $message;
                $sms->provider = $settings->sms_provider;
                $sms->source_addr = $settings->sms_sender_id;
                $sms->status = 'queued';
                $sms->save();
                $destinations[] = ['recipient_id' => $id, 'dest_addr' => $recipient];
            }

            $payload = [
                'source_addr' => $settings->sms_sender_id,
                'schedule_time' => '',
                'encoding' => '0',
                'message' => $message,
                'recipients' => $destinations,
            ];
            $endpoint = $settings->sms_base_url.'/send';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->withBasicAuth(username: $settings->sms_api_key, password: $settings->sms_secret_key)
                ->asJson()
                ->post($endpoint, $payload)
                ->onError(function ($error) {
                    return response()->json(['status' => false, 'message' => $error]);
                });

            $response = (object) json_decode($response->body());
            if ($response->code == 100) {
                \Modules\SMS\Entities\Sms::where('conversation_id', $conversation_id)->update([
                    'request_id' => $response->request_id,
                    'response_message' => $response->message,
                    'status' => 'submitted',
                ]);

                return response()->json(['status' => true, 'message' => $response->message]);

            } else {
                \Modules\SMS\Entities\Sms::where('conversation_id', $conversation_id)->update([
                    'response_message' => $response->message,
                    'status' => 'failed',
                ]);

                return response()->json(['status' => false, 'message' => $response->message]);
            }
        } catch (Throwable $throwable) {
            return response()->json(['status' => false, 'message' => $throwable->getMessage()]);
        }
    }

    public static function sms_conversation_id(): ?string
    {
        $id = uuid_create();
        $status = \Modules\SMS\Entities\Sms::where('conversation_id', $id)->first();
        if (! empty($status)) {
            self::sms_conversation_id();
        }

        return $id;
    }
}
