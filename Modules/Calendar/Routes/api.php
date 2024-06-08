<?php

use Modules\Calendar\Http\Controllers\CalendarController;

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
    Route::get('events', [CalendarController::class, 'index']);
    Route::post('events-create', [CalendarController::class, 'create']);
    Route::post('events-update', [CalendarController::class, 'update']);
    Route::post('events-delete', [CalendarController::class, 'delete']);
});
