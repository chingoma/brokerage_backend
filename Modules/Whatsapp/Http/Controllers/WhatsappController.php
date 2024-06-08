<?php

namespace Modules\Whatsapp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Modules\Whatsapp\Entities\WhatsappSetting;
use Modules\Whatsapp\Helpers\WhatsappHelper;
use Modules\Whatsapp\Helpers\WhatsappMessagesHelper;

class WhatsappController extends Controller
{
    public function verification_requests(Request $request): JsonResponse
    {
        if ($request->hub_verify_token === Config::get('whatsapp.whatsapp_webhook_token')) {
            return response()->json($request->hub_challenge);
        }

        return response()->json();
    }

    public function event_notifications(Request $request): void
    {

    }

    public function subscribe_webhook()
    {
        $response = WhatsappHelper::subscribeWebhook();

        return response()->json($response);
    }

    public function update_settings(Request $request)
    {
        try {
            $settings = WhatsappSetting::first();
            if (empty($settings)) {
                $settings = new WhatsappSetting();
            }
            $settings->whatsapp_id = $request->whatsapp_id;
            $settings->w_business_id = $request->w_business_id;
            $settings->base_url = $request->base_url;
            $settings->access_token = $request->access_token;
            $settings->webhook_token = $request->webhook_token;
            $settings->callback_url = $request->callback_url;
            $settings->status = $request->status;
            $settings->api_version = $request->api_version;
            $settings->save();
            if (! empty($request->recipient)) {
                WhatsappMessagesHelper::sendHelloWorld(recipient: $request->recipient);

                return $this->onSuccessResponse('Settings saved and Message sent');
            }

            return $this->onSuccessResponse('Settings saved');
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settings(Request $request)
    {
        try {
            $settings = WhatsappSetting::first();

            return response()->json($settings);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function send_text_message(Request $request)
    {
        try {
            WhatsappMessagesHelper::sendTextMessage(message: $request->text, recipient: $request->recipient);

            return $this->onSuccessResponse('Message sent');
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function send_hello_world(Request $request)
    {
        try {
            WhatsappMessagesHelper::sendHelloWorld(recipient: $request->recipient);

            return $this->onSuccessResponse('Message sent');
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
