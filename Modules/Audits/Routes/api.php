<?php

use Modules\Audits\Http\Controllers\AuditsController;

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
    Route::get('get-audits-by-auditable', [AuditsController::class, 'getAuditsByAuditable']);
    Route::get('auth-logs', [AuditsController::class, 'auth_logs']);
    Route::get('auth-data', [AuditsController::class, 'auth_data']);
    Route::get('audits', [AuditsController::class, 'audits']);
    Route::get('audits-data', [AuditsController::class, 'audits_data']);
    Route::get('audits-export', [AuditsController::class, 'audits_export']);
});
