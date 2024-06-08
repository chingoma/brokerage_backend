<?php

use Illuminate\Support\Facades\Route;
use Modules\DSE\Http\Controllers\DSEController;

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

Route::get('stakeholders/external/brokers', [DSEController::class, 'brokers']);

Route::prefix('callback')->group(function () {
    Route::any('order-status', [DSEController::class, 'callback_order_status']);
    Route::any('investor-registration', [DSEController::class, 'callback_investor_registration']);
    Route::any('investor-pledge-transaction', [DSEController::class, 'callback_investor_pledge_transaction']);
    Route::any('investor-release-pledged-transaction', [DSEController::class, 'callback_investor_release_pledged_transaction']);
});

Route::get('stakeholders/ipo', [DSEController::class, 'pull_ipo_companies']);
Route::post('stakeholders/ipo/buy-shares', [DSEController::class, 'pull_ipo_companies']);

Route::post('stakeholders/accounts/details', [DSEController::class, 'account_details']);
Route::post('stakeholders/investors/get-investor-holdings', [DSEController::class, 'investor_holdings']);

Route::post('stakeholders/accounts', [DSEController::class, 'create_account']);

Route::post('stakeholders/investors/get-buy-order-details', [DSEController::class, 'get_buy_order_details']);
Route::post('stakeholders/investors/get-sell-order-details', [DSEController::class, 'get_sell_order_details']);
Route::post('stakeholders/investors/pledge-transaction', [DSEController::class, 'pledge_transactions']);
Route::post('stakeholders/investors/release-transaction', [DSEController::class, 'release_transaction']);

Route::post('stakeholders/investors/get-buy-orders', [DSEController::class, 'get_buy_orders']);
Route::post('stakeholders/investors/get-sell-orders', [DSEController::class, 'get_sell_orders']);

Route::post('stakeholders/investors/buy-shares', [DSEController::class, 'buy_shares']);
Route::post('stakeholders/investors/sell-shares', [DSEController::class, 'sell_shares']);

Route::post('stakeholders/investors/get-market-data', [DSEController::class, 'market_data']);
Route::post('stakeholders/investors/get-market-data-statistics', [DSEController::class, 'market_data']);

Route::post('stakeholders/accounts/verify', [DSEController::class, 'verifyAccount']);

Route::post('stakeholders/accounts/verifyLinkage', [DSEController::class, 'verifyLinkage']);

Route::post('create-investor-callback', [DSEController::class, 'create_investor_callback']);
Route::get('signature', [DSEController::class, 'signature']);
