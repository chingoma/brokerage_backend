<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\CustomerCategoriesController;
use Modules\CRM\Http\Controllers\CustomersController;

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


    Route::get('payees', [CustomersController::class, 'payees']);

    Route::post('add-payee', [CustomersController::class, 'add_payee']);

    Route::prefix('customer-categories')->group(function () {
        Route::post('add-category', [CustomerCategoriesController::class, 'add_category']);
        Route::post('delete-category', [CustomerCategoriesController::class, 'delete_category']);
        Route::post('update-category', [CustomerCategoriesController::class, 'update_category']);
        Route::get('categories-data', [CustomerCategoriesController::class, 'categories_data']);
    });

    Route::prefix('customers')->group(function () {

        Route::post('add-custodian', [CustomersController::class, 'add_custodian']);

        Route::any('export-debtors', [CustomersController::class, 'export_debtors']);
        Route::any('export-creditors', [CustomersController::class, 'export_creditors']);
        Route::post('change-password', [CustomersController::class, 'change_password']);
        Route::post('change-email', [CustomersController::class, 'change_email']);
        Route::post('send-reset-password-email', [CustomersController::class, 'send_reset_password_email']);
        Route::post('export-csv', [CustomersController::class, 'export_csv']);
        Route::get('export-csv-all', [CustomersController::class, 'export_csv_all']);
        Route::get('export-wallet', [CustomersController::class, 'export_wallet']);
        Route::post('print-statement', [CustomersController::class, 'downloadStatement']);
        Route::get('statement', [CustomersController::class, 'statement']);
        Route::get('custodians', [CustomersController::class, 'custodians']);
        Route::get('customers-list', [CustomersController::class, 'customersList']);
        Route::get('customer-data', [CustomersController::class, 'profile']);
        Route::get('portfolio', [CustomersController::class, 'portfolio']);
        Route::get('data', [CustomersController::class, 'data_customers']);
        Route::post('update-dse-account', [CustomersController::class, 'update_dse_account']);
        Route::get('customers', [CustomersController::class, 'customers']);
        Route::get('pending-customers-custodians', [CustomersController::class, 'pending_customers_custodians']);
        Route::get('customers-wallet', [CustomersController::class, 'customers_wallet']);
        Route::get('creditors', [CustomersController::class, 'creditors']);
        Route::get('debtors', [CustomersController::class, 'debtors']);
        Route::get('customers-status', [CustomersController::class, 'customers_status']);
        Route::get('customers-kyc', [CustomersController::class, 'customers_kyc']);
        Route::get('customers-onboarding', [CustomersController::class, 'customers_onboarding']);
        Route::get('new-customers', [CustomersController::class, 'new_customers']);
        Route::post('document-create', [CustomersController::class, 'create_document']);
        Route::post('import-customers', [CustomersController::class, 'importCustomers']);
        Route::post('delete-file', [CustomersController::class, 'delete_document']);
        Route::post('customer-update', [CustomersController::class, 'update_profile']);
        Route::post('change-status', [CustomersController::class, 'change_status']);
        Route::post('approve', [CustomersController::class, 'approve']);
        Route::post('approve-kyc', [CustomersController::class, 'approve_kyc']);
        Route::post('hide-customer', [CustomersController::class, 'hideCustomer']);
        Route::post('change-custodian', [CustomersController::class, 'change_custodian']);
        Route::post('approve-custodian', [CustomersController::class, 'approve_custodian']);
        Route::post('reset-failed-attempts', [CustomersController::class, 'reset_failed_attempts']);
        Route::post('change-custodian-status', [CustomersController::class, 'change_custodian_status']);

        Route::post('customer-update-cooperate', [CustomersController::class, 'change_cooperate']);

        Route::post('customer-existing', [CustomersController::class, 'create_customer_existing']);
        Route::post('customer-create', [CustomersController::class, 'create_customer_individual']);
        Route::post('inspect-account', [CustomersController::class, 'inspect_account']);
        Route::post('verify-dse-account', [CustomersController::class, 'verify_dse_account']);
        Route::post('fetch-dse-account', [CustomersController::class, 'fetch_dse_account']);
        Route::post('customer-update-individual', [CustomersController::class, 'update_customer_individual']);

        Route::post('customer-create-minor', [CustomersController::class, 'create_customer_minor']);
        Route::post('customer-update-minor', [CustomersController::class, 'update_customer_minor']);
        Route::post('upgrade-minor', [CustomersController::class, 'upgrade_minor']);

        Route::post('upgrade-individual', [CustomersController::class, 'upgrade_individual']);
        Route::post('downgrade-individual', [CustomersController::class, 'downgrade_individual']);

        Route::post('customer-create-joint', [CustomersController::class, 'create_customer_joint']);
        Route::post('customer-update-joint', [CustomersController::class, 'update_customer_joint']);
        Route::post('downgrade-joint', [CustomersController::class, 'downgrade_joint']);

        Route::post('customer-create-corporate', [CustomersController::class, 'create_customer_corporate']);
        Route::post('customer-update-corporate', [CustomersController::class, 'update_customer_corporate']);

        Route::post('migrate-shares', [CustomersController::class, 'migrate_shares']);
        Route::post('migrate-bond', [CustomersController::class, 'migrate_bond']);

        Route::post('sync-DSE', [CustomersController::class, 'syncDSE']);
    });

});
