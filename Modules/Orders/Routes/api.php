<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrdersController;

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
    Route::get('unmatched', [OrdersController::class, 'orders_unmatched']);
    Route::get('unmatched-filter-month', [OrdersController::class, 'unmatched_filter_month']);
    Route::get('orders-reconcile', [OrdersController::class, 'orders_reconcile']);
    Route::get('reconcile-filter-month', [OrdersController::class, 'reconcile_filter_month']);
    Route::post('import-reconcile', [OrdersController::class, 'orders_reconcile_import']);
    Route::prefix('exports')->group(function () {
        Route::get('all-orders', [OrdersController::class, 'export_all_orders']);
        Route::get('all-months', [OrdersController::class, 'export_all_months']);
        Route::get('monthly-orders', [OrdersController::class, 'export_monthly']);
    });
    Route::get('filter', [OrdersController::class, 'filter']);
    Route::post('download-pdf', [OrdersController::class, 'downloadPdf']);
    Route::post('order-export-csv', [OrdersController::class, 'export_csv']);
    Route::get('orders-data', [OrdersController::class, 'orders_data']);
    Route::get('order', [OrdersController::class, 'order']);
    Route::get('all-orders', [OrdersController::class, 'all_orders']);
    Route::get('order-overdraft', [OrdersController::class, 'order_overdraft']);
    Route::post('order-overdraft-update', [OrdersController::class, 'update_overdraft_order']);
    Route::post('approve-overdraft', [OrdersController::class, 'approve_overdraft']);
    Route::post('reject-overdraft', [OrdersController::class, 'reject_overdraft']);
    Route::post('reject-overdraft-selected', [OrdersController::class, 'rejectSelectedOverdraft']);
    Route::post('approve-overdraft-selected', [OrdersController::class, 'approveSelectedOverdraft']);
    Route::get('all-orders-status', [OrdersController::class, 'all_orders_status']);
    Route::get('orders-request-cancel', [OrdersController::class, 'orders_request_cancel']);
    Route::get('new-orders', [OrdersController::class, 'new_orders']);
    Route::post('order-create', [OrdersController::class, 'store']);
    Route::post('order-create-buy', [OrdersController::class, 'createBuy']);
    Route::post('order-create-sell', [OrdersController::class, 'createSell']);
    Route::post('document-create', [OrdersController::class, 'create_document']);
    Route::post('delete-file', [OrdersController::class, 'delete_document']);
    Route::post('order-update', [OrdersController::class, 'update_order']);
    Route::post('change-status', [OrdersController::class, 'update_status']);
    Route::post('approve-order', [OrdersController::class, 'approve']);
    Route::post('cancel-order', [OrdersController::class, 'cancel_order']);
    Route::post('close-open', [OrdersController::class, 'close_open']);
});