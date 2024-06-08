<?php

use Illuminate\Http\Request;
use Modules\Custodians\Http\Controllers\CustodiansController;

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

Route::middleware('auth:api')->get('/custodians', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum', 'isAdmin']], function () {
    Route::get('custodians/list', [CustodiansController::class, 'custodians']);
    Route::post('custodians/update-custodian', [CustodiansController::class, 'update']);
    Route::post('custodians/delete-custodian', [CustodiansController::class, 'delete']);
    Route::post('custodians/add-custodian', [CustodiansController::class, 'store']);
});
