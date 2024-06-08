<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

Route::get('holding', [TradeReportsController::class, 'downloadMasterHoldingReport']);

Route::group(['middleware' => ['auth:sanctum', 'isAllowedIp']], function () {

Route::get("get-files", function (Request $request) {
    return Storage::disk('main_storage')->get($request->path.'/'.$request->file);
});

});
