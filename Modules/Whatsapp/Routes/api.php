<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Whatsapp\Http\Controllers\WhatsappController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/


Route::get('callback', function (Request $request) {

    $settings = \Modules\Whatsapp\Entities\WhatsappSetting::first();
    if ($request->hub_verify_token === $settings->webhook_token) {
        return $request->hub_challenge;
    }

    return response()->json();
});

Route::post('callback', [WhatsappController::class, 'event_notifications']);

Route::post('subscribe-webhook', [WhatsappController::class, 'subscribe_webhook']);

Route::post('send-text-message', [WhatsappController::class, 'send_text_message']);

Route::post('send-hello-world', [WhatsappController::class, 'send_text_message']);

Route::post('update-settings', [WhatsappController::class, 'update_settings']);

Route::get('settings', [WhatsappController::class, 'settings']);
