<?php

use Illuminate\Support\Facades\Route;
use Modules\EndOfDayReport\Http\Controllers\EndOfDayReportController;

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

Route::group(['middleware' => ['auth:sanctum','isAdmin']], function() {
    Route::post('end-of-day/generateReport', [EndOfDayReportController::class, 'generateReport']);
    Route::post('end-of-day/regenerateReport', [EndOfDayReportController::class, 'regenerateReport']);
    Route::any('end-of-day/end-day', [EndOfDayReportController::class, 'endDay']);
    Route::any('end-of-day/rollback', [EndOfDayReportController::class, 'rollbackDay']);
    Route::get('end-of-day/statuses', [EndOfDayReportController::class, 'statuses']);
    Route::get('end-of-day/reports', [EndOfDayReportController::class, '_list']);
});

