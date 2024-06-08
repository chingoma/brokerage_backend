<?php

use Illuminate\Support\Facades\Route;
use Modules\Receipts\Http\Controllers\ReceiptsController;

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
    Route::get('meta-data', [ReceiptsController::class, 'meta_data']);
    Route::get('list', [ReceiptsController::class, 'receipts']);
    Route::post('create', [ReceiptsController::class, 'store']);
    Route::post('create-multiple', [ReceiptsController::class, 'create_multiple']);
});
