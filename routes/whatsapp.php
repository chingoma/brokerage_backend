<?php

use App\Helpers\MessagingHelper;
use App\Http\Controllers\WhatsappController;
use App\Models\Messaging\Message;
use Illuminate\Support\Facades\Route;

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

Route::any('/', [WhatsappController::class, 'verify']);

Route::get('events', function () {
    $message = new Message();
    $event = new MessagingHelper($message);
    event($event);
});
