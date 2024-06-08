<?php

use Illuminate\Support\Facades\Route;
use Modules\Bonds\Http\Controllers\BondsController;
use Modules\DealingSheets\Http\Controllers\DealingSheetsController;

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
    Route::post('send-contract-note', [DealingSheetsController::class, 'send_contract_note']);
//    Route::apiResource('list', BondsController::class);
    Route::get('settings', [BondsController::class, 'trades']);

    Route::get('trades', [DealingSheetsController::class, 'trades']);
    Route::get('sheets', [DealingSheetsController::class, 'all_orders']);
    Route::post('export-csv', [DealingSheetsController::class, 'export_csv']);
    Route::get('sheets-customer', [DealingSheetsController::class, 'all_sheets_customer']);
    Route::get('sheets-status', [DealingSheetsController::class, 'all_sheets_status']);
    Route::get('sheet', [DealingSheetsController::class, 'sheet']);
    Route::get('sheet-reference', [DealingSheetsController::class, 'sheet_reference']);
    Route::post('sheet-create', [DealingSheetsController::class, 'store']);
    Route::post('sheet-update', [DealingSheetsController::class, 'sheet_update']);
    Route::post('sheet-status-update', [DealingSheetsController::class, 'update_status']);
    Route::post('document-create', [DealingSheetsController::class, 'create_document']);
    Route::post('delete-file', [DealingSheetsController::class, 'delete_document']);
    Route::post('execute-order', [DealingSheetsController::class, 'execute_order']);
});
