<?php

use Illuminate\Support\Facades\Route;
use Modules\Cards\Http\Controllers\CardsController;

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
    Route::get('top-customers-equities', [CardsController::class, 'topCustomersEquities']);
    Route::get('top-customers-bonds', [CardsController::class, 'topCustomersBonds']);
    Route::get('equity-bond-revenue', [CardsController::class, 'equityBondRevenue']);
});
