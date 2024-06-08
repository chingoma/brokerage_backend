<?php

use Illuminate\Support\Facades\Route;
use Modules\Securities\Http\Controllers\SecuritiesController;

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
    Route::get("list",[SecuritiesController::class,'index']);
    Route::get("investors",[SecuritiesController::class,'investors']);
    Route::post("investors-export",[SecuritiesController::class,'investors_download']);
    Route::get("details",[SecuritiesController::class,'details']);
});
