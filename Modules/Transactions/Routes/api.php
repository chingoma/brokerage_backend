<?php

use Modules\Transactions\Http\Controllers\TransactionsController;

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
    Route::get('all', [TransactionsController::class, 'all']);
    Route::get('pending', [TransactionsController::class, 'pending']);
    Route::get('pending-sync', [TransactionsController::class, 'pending_sync']);
    Route::get('statements', [TransactionsController::class, 'statements']);
    Route::get('statements-customer', [TransactionsController::class, 'statements_customer']);
    Route::get('payments', [TransactionsController::class, 'payments']);
    Route::get('receipts', [TransactionsController::class, 'receipts']);
    Route::get('equities', [TransactionsController::class, 'equities']);
    Route::get('bonds', [TransactionsController::class, 'bonds']);
    Route::get('custodians', [TransactionsController::class, 'custodians']);
    Route::post('reject', [TransactionsController::class, 'reject']);
    Route::post('approve', [TransactionsController::class, 'approve']);
    Route::post('post-to-flexcube', [TransactionsController::class, 'post_to_flexcube']);
    Route::post('do-not-post-to-flexcube', [TransactionsController::class, 'do_not_post_to_flexcube']);

    Route::get('transaction-data', [TransactionsController::class, 'transaction']);
    Route::get('reference', [TransactionsController::class, 'reference']);
});
