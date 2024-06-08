<?php

use Modules\MarketData\Http\Controllers\MarketDataController;

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
    Route::get('companies', [MarketDataController::class, 'companies']);
    Route::get('data', [MarketDataController::class, 'market_data']);
    Route::get('company', [MarketDataController::class, 'company']);
    Route::get('data-filter', [MarketDataController::class, 'market_data_filter']);
    Route::post('add-market-data', [MarketDataController::class, 'add_market_data']);
});
