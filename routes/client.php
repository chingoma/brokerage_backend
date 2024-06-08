<?php

use App\Http\Controllers\Accounting\ClientTransactionsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Settings\AccountController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'isClient']], function () {

    Route::prefix('transactions')->group(function () {
        Route::get('transaction-data', [ClientTransactionsController::class, 'transaction']);
        Route::get('transactions-settings', [ClientTransactionsController::class, 'settings']);
        Route::get('all-transactions', [ClientTransactionsController::class, 'all_transactions']);
        Route::get('all-transactions-status', [ClientTransactionsController::class, 'all_transactions_status']);
        Route::get('all-transactions-type-status', [ClientTransactionsController::class, 'all_transactions_type_status']);
        Route::get('new-transactions', [ClientTransactionsController::class, 'new_transactions']);
        Route::post('transaction-create', [ClientTransactionsController::class, 'create_transaction']);
        Route::post('document-create', [ClientTransactionsController::class, 'create_document']);
        Route::post('delete-file', [ClientTransactionsController::class, 'delete_document']);
        Route::post('transaction-update', [ClientTransactionsController::class, 'update_transaction']);
        Route::post('transaction-update-status', [ClientTransactionsController::class, 'update_status']);
    });


    Route::prefix('profile')->group(function () {
        Route::get('profile-data', [AccountController::class, 'profile_data']);
        Route::get('profile-activities', [AccountController::class, 'profile_activities']);
    });


});

Route::post('login', [AuthController::class, 'login']);

Route::post('register', [AuthController::class, 'register']);

Route::post('send-verify-account', [AuthController::class, 'send_verification_link']);

Route::post('verify-account', [AuthController::class, 'verify_account']);

Route::post('send-password-reset', [AuthController::class, 'send_password_reset']);

Route::post('password-reset', [AuthController::class, 'password_reset']);
