<?php

use Modules\SMS\Http\Controllers\SMSController;

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

Route::group(['middleware' => ['auth:sanctum', 'isAdmin']], function () {
    Route::get('settings', [SMSController::class, 'settings']);
    Route::post('update-settings', [SMSController::class, 'update_settings']);
});
