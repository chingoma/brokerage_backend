<?php

use Illuminate\Support\Facades\Route;
use Modules\Bonds\Http\Controllers\BondExecutionsController;
use Modules\Bonds\Http\Controllers\BondOrdersController;
use Modules\Bonds\Http\Controllers\BondsController;

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
    Route::apiResource('list', BondsController::class);
    Route::get('settings', [BondordersController::class, 'settings']);
    Route::post('order-create', [BondordersController::class, 'store']);
    Route::post('buy-order', [BondordersController::class, 'buy_order']);
    Route::post('sell-order', [BondordersController::class, 'sell_order']);
    Route::post('order-update', [BondordersController::class, 'update_order']);
    Route::post('order-overdraft-update', [BondordersController::class, 'update_overdraft_order']);
    Route::post('approve-overdraft', [BondordersController::class, 'approve_overdraft']);
    Route::post('approve-overdraft', [BondordersController::class, 'approve_overdraft']);
    Route::post('reject-overdraft', [BondordersController::class, 'reject_overdraft']);
    Route::post('reject-overdraft-selected', [BondordersController::class, 'rejectSelectedOverdraft']);
    Route::post('approve-overdraft-selected', [BondordersController::class, 'approveSelectedOverdraft']);
    Route::post('review-order', [BondordersController::class, 'review']);
    Route::post('approve-order', [BondordersController::class, 'approve']);
    Route::post('reject-order', [BondordersController::class, 'approve']);
    Route::post('change-status', [BondordersController::class, 'update_status']);
    Route::get('orders', [BondordersController::class, 'orders']);
    Route::get('orders-overdraft', [BondordersController::class, 'orders_overdraft']);
    Route::get('order', [BondordersController::class, 'order']);
    Route::get('confirmations', [BondExecutionsController::class, 'confirmations']);
    Route::get('dealing-sheets', [BondExecutionsController::class, 'dealing_sheets']);
    Route::get('confirmation', [BondExecutionsController::class, 'confirmation']);
    Route::get('order-confirmations', [BondExecutionsController::class, 'order_confirmations']);
    Route::get('customer-confirmations', [BondExecutionsController::class, 'customer_confirmations']);
    Route::post('confirmation-create', [BondExecutionsController::class, 'store']);
    Route::post('confirmation-update', [BondExecutionsController::class, 'confirmation_update']);
    Route::post('add-auctions', [BondsController::class, 'add_auctions']);
    Route::post('update-auctions', [BondsController::class, 'update_auctions']);
    Route::get('auctions', [BondsController::class, 'auctions']);

    Route::get('confirmation-by-reference', [BondExecutionsController::class, 'confirmation_by_reference']);
    Route::post('confirmation-status-update', [BondExecutionsController::class, 'update_status']);
    Route::post('confirmation-approve', [BondExecutionsController::class, 'approve']);
    Route::post('confirmation-reject', [BondExecutionsController::class, 'reject']);
    Route::post('download-contract-note', [BondExecutionsController::class, 'downloadContractNote']);
    Route::post('send-contract-note', [BondExecutionsController::class, 'send_contract_note']);
});
