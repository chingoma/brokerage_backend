<?php

use Illuminate\Support\Facades\Route;
use Modules\DSE\Http\Controllers\DSEController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('callback/account-create', function (Request $request) {
    echo 'callback response';
});

Route::group([], function () {
    Route::resource('dse', DSEController::class)->names('dse');
});

Route::get('token', [DSEController::class, 'create_token']);
