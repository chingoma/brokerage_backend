<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Http\Controllers\PaymentsController;

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
    Route::get('meta-data', [PaymentsController::class, 'meta_data']);
    Route::get('list', [PaymentsController::class, 'payments']);
    Route::post('create', [PaymentsController::class, 'store']);
    Route::post('create-multiple', [PaymentsController::class, 'create_multiple']);
});
