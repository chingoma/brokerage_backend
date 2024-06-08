<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\FinanceReportsController;
use Modules\Reports\Http\Controllers\TradeReportsController;

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
    Route::prefix('trading')->group(function () {

        Route::get('settings', [TradeReportsController::class, 'settings']);
        Route::get('customer-holdings', [TradeReportsController::class, 'customer_holdings']);
        Route::post('download-customer-holding-report', [TradeReportsController::class, 'downloadCustomerHoldingReport']);
        Route::post('download-master-holding-report', [TradeReportsController::class, 'downloadMasterHoldingReport']);
        Route::post('send-customer-holding-report', [TradeReportsController::class, 'sendCustomerHoldingReport']);
        Route::get('emails-equity', [TradeReportsController::class, 'unsent_email_equity']);
        Route::get('emails-bond', [TradeReportsController::class, 'unsent_email_bond']);
    });
    Route::prefix('finance')->group(function () {

        Route::get('settings', [FinanceReportsController::class, 'settings']);
        Route::get('trade-register-report-filter', [FinanceReportsController::class, 'trade_register_report_filter']);
        Route::get('settlement-report-filter', [FinanceReportsController::class, 'settlement_report_filter']);
        Route::post('trade-register-report-export', [FinanceReportsController::class, 'trade_register_report_export']);
        Route::post('settlement-report-export', [FinanceReportsController::class, 'settlement_report_export']);
        Route::post('flexcube-report-export', [FinanceReportsController::class, 'flexcube_entries_report_export']);
        Route::post('custodian-report-export', [FinanceReportsController::class, 'custodian_report_export']);

    });
});
