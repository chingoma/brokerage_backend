<?php

use Illuminate\Support\Facades\Route;
use Modules\NIDA\Http\Controllers\NIDAController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::group([], function () {
    Route::any('/get-nida', [NIDAController::class,"getNida"]);
});