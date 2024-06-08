<?php

use Illuminate\Support\Facades\Route;
use Modules\Assets\Http\Controllers\AssetCategoriesController;
use Modules\Assets\Http\Controllers\AssetIssuesController;
use Modules\Assets\Http\Controllers\AssetRequestsController;
use Modules\Assets\Http\Controllers\AssetsController;
use Modules\Assets\Http\Controllers\AssetSubCategoriesController;

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

    Route::apiResource('/', AssetsController::class);
    Route::apiResource('/asset-categories', AssetCategoriesController::class);
    Route::apiResource('/asset-sub-categories', AssetSubCategoriesController::class);
    Route::apiResource('/requests', AssetRequestsController::class);
    Route::apiResource('/issues', AssetIssuesController::class);

    Route::get('/settings', [AssetsController::class, 'Settings']);
    Route::get('/category-sub-categories/{id}', [AssetCategoriesController::class, 'SubCategories']);
    Route::get('/asset-categories-settings', [AssetCategoriesController::class, 'Settings']);
    Route::get('/asset-sub-categories-settings', [AssetSubCategoriesController::class, 'Settings']);

});
