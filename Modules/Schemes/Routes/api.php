<?php

use Illuminate\Support\Facades\Route;
use Modules\Schemes\Http\Controllers\BondSchemesController;
use Modules\Schemes\Http\Controllers\EquitySchemesController;

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
    Route::prefix('equities')->group(function () {
        Route::get('list', [EquitySchemesController::class, 'index']);
        Route::get('show', [EquitySchemesController::class, 'show']);
        Route::post('store', [EquitySchemesController::class, 'store']);
        Route::post('update', [EquitySchemesController::class, 'update']);
        Route::post('destroy', [EquitySchemesController::class, 'destroy']);
    });

    Route::prefix('bonds')->group(function () {
        Route::get('list', [BondSchemesController::class, 'index']);
        Route::get('show', [BondSchemesController::class, 'show']);
        Route::post('store', [BondSchemesController::class, 'store']);
        Route::post('update', [BondSchemesController::class, 'update']);
        Route::post('destroy', [BondSchemesController::class, 'destroy']);
    });
});
