<?php

use Illuminate\Support\Facades\Route;
use Modules\Expenses\Http\Controllers\ExpensesController;

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

    Route::prefix('expenses')->group(function () {
        Route::get('settings-data', [ExpensesController::class, 'settings_data']);
        Route::get('/', [ExpensesController::class, 'expenses']);
        Route::get('filter', [ExpensesController::class, 'filter']);
        Route::post('approve-selected', [ExpensesController::class, 'approveSelected']);
        Route::post('reject-selected', [ExpensesController::class, 'rejectSelected']);
        Route::post('dis-approve-selected', [ExpensesController::class, 'disApproveSelected']);
        Route::post('create-expense-cheque', [ExpensesController::class, 'create_expense_cheque']);
        Route::post('print-range', [ExpensesController::class, 'export_range']);
        Route::get('print-monthly', [ExpensesController::class, 'export_monthly']);
        Route::get('all-months', [ExpensesController::class, 'export_all_months']);
        Route::get('report', [ExpensesController::class, 'expenses_report']);
        Route::get('stats', [ExpensesController::class, 'stats']);
    });

});
