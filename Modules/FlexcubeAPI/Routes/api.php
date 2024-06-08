<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\FlexcubeAPI\Http\Controllers\FlexcubeAPIController;

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


Route::group( [ 'prefix' => 'api' ], function () {

    //posting
    Route::post("/createdemultioffset",[FlexcubeAPIController::class,'store']);

    //
    Route::get("/",[FlexcubeAPIController::class,'index']);
});
