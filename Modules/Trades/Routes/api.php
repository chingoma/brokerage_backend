<?php

use Modules\Trades\Http\Controllers\TradesController;

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
    Route::get('stats', [TradesController::class, 'stats']);
    Route::get('list', [TradesController::class, 'trades']);
    Route::get('search', [TradesController::class, 'search_trade']);
    Route::get('filter', [TradesController::class, 'filter_trade']);
    Route::get('filter-timely', [TradesController::class, 'filter_timely']);
    Route::post('export', [TradesController::class, 'export']);
});
