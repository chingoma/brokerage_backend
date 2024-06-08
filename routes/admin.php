<?php

use App\Helpers\AdminProfile;
use App\Helpers\Helper;
use App\Http\Controllers\Accounting\AccountReconcileController;
use App\Http\Controllers\Accounting\AccountsController;
use App\Http\Controllers\Accounting\AccountsReportsController;
use App\Http\Controllers\Accounting\CategoryController;
use App\Http\Controllers\Accounting\CmsaReportController;
use App\Http\Controllers\Accounting\CsdrReportController;
use App\Http\Controllers\Accounting\DseReportController;
use App\Http\Controllers\Accounting\FidelityReportController;
use App\Http\Controllers\Accounting\GeneralLedgerController;
use App\Http\Controllers\Accounting\JournalsController;
use App\Http\Controllers\Accounting\PaymentMethodController;
use App\Http\Controllers\Accounting\PaymentsController;
use App\Http\Controllers\Accounting\RealAccountController;
use App\Http\Controllers\Accounting\ReceiptsController;
use App\Http\Controllers\Accounting\SettingsController;
use App\Http\Controllers\Accounting\TaxController;
use App\Http\Controllers\Accounting\TransactionsController;
use App\Http\Controllers\Accounting\UnitController;
use App\Http\Controllers\Accounting\VatReportController;
use App\Http\Controllers\Accounting\VouchersController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BanksController;
use App\Http\Controllers\Chats\ChatsController;
use App\Http\Controllers\Chatting\ChattingController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\HumanResource\EmployeesController;
use App\Http\Controllers\NewsLetter\CustomReportsController;
use App\Http\Controllers\NewsLetter\WeeklyReportsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\SectorsController;
use App\Http\Controllers\Settings\AccountController;
use App\Models\MarketDataStockPostgres;
use App\Models\Role;
use App\Models\Security;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Modules\Calendar\Entities\Calendar;
use Modules\CRM\Http\Controllers\CustomersController;
use Modules\DealingSheets\Http\Controllers\DealingSheetsController;
use Modules\Orders\Http\Controllers\OrdersController;

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

Route::get("ps", function (){

    dd(\Modules\MarketData\Entities\InvestorsData::get());

//    Schema::connection('pulse')->create('pulse_values', function (Blueprint $table) {
//        $table->id();
//        $table->unsignedInteger('timestamp');
//        $table->string('type');
//        $table->mediumText('key');
//        $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))');
//
//        $table->mediumText('value');
//
//        $table->index('timestamp'); // For trimming...
//        $table->index('type'); // For fast lookups and purging...
//        $table->unique(['type', 'key_hash']); // For data integrity and upserts...
//    });

//    Schema::connection('pulse')->create('pulse_entries', function (Blueprint $table) {
//        $table->id();
//        $table->unsignedInteger('timestamp');
//        $table->string('type');
//        $table->mediumText('key');
//        $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))');
//
//        $table->bigInteger('value')->nullable();
//
//        $table->index('timestamp'); // For trimming...
//        $table->index('type'); // For purging...
//        $table->index('key_hash'); // For mapping...
//        $table->index(['timestamp', 'type', 'key_hash', 'value']); // For aggregate queries...
//    });

    Schema::connection('pulse')->create('pulse_aggregates', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('bucket');
        $table->unsignedMediumInteger('period');
        $table->string('type');
        $table->mediumText('key');
        $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))');
        $table->string('aggregate');
        $table->decimal('value', 20, 2);
        $table->unsignedInteger('count')->nullable();

        $table->unique(['bucket', 'period', 'type', 'aggregate', 'key_hash']); // Force "on duplicate update"...
        $table->index(['period', 'bucket']); // For trimming...
        $table->index('type'); // For purging...
        $table->index(['period', 'type', 'aggregate', 'bucket']); // For aggregate queries...
    });

    $dateRaw = Helper::systemDateTime();
    $result = \App\Models\MarketDataBondPostgres::get();
    dd($result);
});

Route::get("print-dealing-sheet",[DealingSheetsController::class,'downloadPdf']);

Route::get("php-info", function(){
    $systemDate = Helper::systemDateTime();
    $order = \Modules\Bonds\Entities\BondExecution::findOrFail(request()->get("id"));
    $order->trade_date = $systemDate['timely'];
    $order->settlement_date = Helper::settlementDateBond($systemDate['today']);
    $order->save();
});

//Route::get("php-info", function(){
//    $systemDate = Helper::systemDateTime();
//    $order = \App\Models\DealingSheet::findOrFail(request()->get("id"));
//    $order->trade_date = $systemDate['timely'];
//    $order->settlement_date = Helper::settlementDateEquity($systemDate['today']);
//    $order->save();
//});

Route::get('callback',function (){
    $request = request();
    if($request->hub_verify_token === Config::get("whatsapp.whatsapp_webhook_token")){
        return response()->json($request->hub_challenge);
    }
    return response()->json();
});

Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::get('logout-api', [AuthController::class,'logout']);

    Route::get("system-date", function (){
        return response()->json(\App\Helpers\Helper::systemDateTime());
    });

    Route::post("change-email",[App\Http\Controllers\Controller::class,'change_email']);

    Route::get("banks/list",[BanksController::class,'banks']);
    Route::post("banks/update-bank",[BanksController::class,'update']);
    Route::post("banks/delete-bank",[BanksController::class,'delete']);
    Route::post("banks/add-bank",[BanksController::class,'store']);

    Route::get("sectors/list",[SectorsController::class,'sectors']);
    Route::post("sectors/update-sector",[SectorsController::class,'update']);
    Route::post("sectors/delete-sector",[SectorsController::class,'delete']);
    Route::post("sectors/add-sector",[SectorsController::class,'store']);

    Route::Post("download-file",[CustomersController::class,'downloadFiles']);
    Route::Post("download-passport",[CustomersController::class,'downloadPassport']);
    Route::Post("download-identity",[CustomersController::class,'downloadIdentity']);

    Route::post("print-dealing-sheet",[DealingSheetsController::class,'downloadPdf']);

    Route::get("print-order",[OrdersController::class,'downloadPdf']);
    Route::Post("print-order",[OrdersController::class,'downloadPdf']);

    Route::get("print-receipt",[TransactionsController::class,'printReceipt']);
    Route::post("print-receipt",[TransactionsController::class,'printReceipt']);

    Route::get("print-payment",[TransactionsController::class,'printPayment']);
    Route::post("print-payment",[TransactionsController::class,'printPayment']);

    Route::get("print-statement",[CustomersController::class,'downloadStatement']);
    Route::post("print-statement",[CustomersController::class,'downloadStatement']);


    Route::post("change-user-password", [AuthController::class,'change_user_email']);

    Route::prefix("chatting")->group( function (){
        Route::get("contacts",[ChattingController::class,'contacts']);
        Route::get("chats",[ChattingController::class,'chats']);
        Route::get("friends",[ChattingController::class,'friends']);
        Route::get("profile",[ChattingController::class,'profile']);
        Route::post("create",[ChattingController::class,'create']);
    });

    Route::prefix("chats")->group( function (){
        Route::get("stock-analysis",[ChatsController::class,'stockAnalysis']);
        Route::get("cash-flow",[ChatsController::class,'cashFlowData']);
    });


    Route::get("order-stats",[OrdersController::class, 'order_stats']);

    Route::get("search-order",[OrdersController::class,'search_order']);

    Route::get("customers-stats",[CustomersController::class,'customers_stats']);

    Route::get("search-customer",[CustomersController::class,'search_customer']);

    Route::get("search-dealing-sheet",[DealingSheetsController::class,'search']);

    Route::prefix("market-reports")->group( function(){

        Route::apiResource("custom-reports",CustomReportsController::class);
        Route::post("custom-reports/update",[CustomReportsController::class,"update"]);
        Route::post("custom-reports/send",[CustomReportsController::class,"send"]);
        Route::get("custom-reports-data",[CustomReportsController::class,"data"]);

        Route::prefix("weekly-reports")->group( function (){
            Route::get("reports",[WeeklyReportsController::class,'reports']);
            Route::post("create",[WeeklyReportsController::class,'create']);
            Route::post("update",[WeeklyReportsController::class,'update']);
            Route::post("regenerate",[WeeklyReportsController::class,'regenerate']);
            Route::post("send",[WeeklyReportsController::class,'send']);
        });
    });

    Route::get("permissions", function (){
        $role = Role::where("name",auth()->user()->getRoleNames()[0]??"")->first();
        $permissions =  $role->getAllPermissions();
        foreach ($permissions as $permission){
            $permissionList[] = str_replace(" ","_",strtolower($permission));
        }
        return  $permissionList;
    });

    Route::prefix("notifications")->group( function(){
        Route::get("notifications",[NotificationsController::class,'notifications']);
    });

    Route::prefix("dashboard")->group( function(){
        Route::get("statistics",[DashboardController::class,'statistics']);
    });

    Route::prefix("accounting")->group( function(){

        Route::apiResource('reconciliation', RealAccountController::class);

        Route::post('reconcile-selected', [AccountReconcileController::class,'reconcile_selected']);
        Route::get('reconciled', [AccountReconcileController::class,'reconciled']);
        Route::post('un-reconcile', [AccountReconcileController::class,'un_reconcile']);
        Route::get('un-reconciled', [AccountReconcileController::class,'un_reconciled']);
        Route::get('reconciliation-stats', [AccountReconcileController::class,'stats']);
        Route::get('reconcile-payments', [AccountReconcileController::class,'reconcile_payments']);
        Route::get('reconcile-receipts', [AccountReconcileController::class,'reconcile_receipts']);
        Route::get('reconcile-orders', [AccountReconcileController::class,'reconcile_orders']);
        Route::post('reconcile-payments-selected', [AccountReconcileController::class,'reconcile_payments_selected']);
        Route::post('reconcile-receipts-selected', [AccountReconcileController::class,'reconcile_receipts_selected']);
        Route::post('reconcile-orders-selected', [AccountReconcileController::class,'reconcile_orders_selected']);

        Route::prefix("reports")->group( function(){

            Route::prefix("income-reports")->group( function (){
                Route::post("print-range",[AccountsReportsController::class,'export_range']);
                Route::get("print-monthly",[AccountsReportsController::class,'downloadPdf']);
                Route::get("all-months",[AccountsReportsController::class,'export_all_months']);
                Route::get("income", [AccountsReportsController::class,'incomes']);
                Route::get("stats", [AccountsReportsController::class,'incomeStats']);
            });

            Route::prefix("expense-reports")->group( function (){
                Route::post("print-range",[ReceiptsController::class,'export_range']);
                Route::get("print-monthly",[ReceiptsController::class,'export_monthly']);
                Route::get("all-months",[ReceiptsController::class,'export_all_months']);
                Route::get("expense", [AccountsReportsController::class,'expenses']);
            });

            Route::prefix("receipts-reports")->group( function (){
                Route::post("print-range",[ReceiptsController::class,'export_range']);
                Route::get("print-monthly",[ReceiptsController::class,'export_monthly']);
                Route::get("all-months",[ReceiptsController::class,'export_all_months']);
                Route::get("receipts", [ReceiptsController::class,'receipts_report']);
            });

            Route::prefix("payments-reports")->group( function (){
                Route::post("print-range",[PaymentsController::class,'export_range']);
                Route::get("print-monthly",[PaymentsController::class,'export_monthly']);
                Route::get("all-months",[PaymentsController::class,'export_all_months']);
                Route::get("payments", [PaymentsController::class,'payments_report']);
            });

            Route::prefix("vat-reports")->group( function (){
                Route::get("print-monthly",[VatReportController::class, 'export_monthly']);
                Route::get("all-months",[VatReportController::class,'export_all_months']);
                Route::post("print-range",[VatReportController::class,'export_range']);
                Route::get("output", [VatReportController::class, 'output']);
                Route::get("input", [VatReportController::class, 'input']);
                Route::get("stats", [VatReportController::class,'stats']);
            });

            Route::prefix("dse-reports")->group( function (){
                Route::get("collected", [DseReportController::class,'collected']);
                Route::get("paid", [DseReportController::class,'paid']);
                Route::get("stats", [DseReportController::class,'stats']);
            });

            Route::prefix("cmsa-reports")->group( function (){
                Route::get("collected", [CmsaReportController::class,'collected']);
                Route::get("paid", [CmsaReportController::class,'paid']);
                Route::get("stats", [CmsaReportController::class,'stats']);
            });

            Route::prefix("fidelity-reports")->group( function (){
                Route::get("collected", [FidelityReportController::class,'collected']);
                Route::get("paid", [FidelityReportController::class,'paid']);
                Route::get("stats", [FidelityReportController::class,'stats']);
            });

            Route::prefix("csdr-reports")->group( function (){
                Route::get("collected", [CsdrReportController::class,'collected']);
                Route::get("paid", [CsdrReportController::class,'paid']);
                Route::get("stats", [CsdrReportController::class,'stats']);
            });
        });

        Route::prefix("settings")->group( function(){
            Route::apiResource('payment-methods', PaymentMethodController::class);
            Route::apiResource('taxes', TaxController::class);
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('units', UnitController::class);

            Route::get("settings-data",[SettingsController::class,'settings_data']);
            Route::get("settings-accounts",[SettingsController::class,'accounts']);
            Route::get("settings-payees",[SettingsController::class,'payees']);
            Route::get("settings-order",[SettingsController::class,'order_settings']);
            Route::post("add-payment-method",[SettingsController::class,'add_payment_method']);
            Route::post("delete-payment-method",[SettingsController::class,'delete_payment_method']);
            Route::post("update-payment-method",[SettingsController::class,'update_payment_method']);
            Route::post("set-customer-settings",[SettingsController::class,'set_customer_settings']);
            Route::post("set-receipt-settings",[SettingsController::class,'set_receipt_settings']);
            Route::post("set-order-settings",[SettingsController::class,'set_order_settings']);
            Route::post("set-bill-settings",[SettingsController::class,'set_bill_settings']);
        });

        Route::prefix("accounts")->group( function(){
            Route::get("settings-data",[AccountsController::class,'settings_data']);
            Route::post("create",[AccountsController::class,'create']);
            Route::get("/",[AccountsController::class,'accounts']);
            Route::get("trial-balance",[AccountsController::class,'trialBalance']);
            Route::get("balance-sheet",[AccountsController::class,'balanceSheet']);
            Route::get("financial-statement",[AccountsController::class,'financialStatement']);
            Route::get("account",[AccountsController::class,'account']);
            Route::post("edit",[AccountsController::class,'edit']);
            Route::post("delete",[AccountsController::class,'delete']);
        });

        Route::prefix("ledgers")->group( function(){
            Route::get("/",[GeneralLedgerController::class,'ledgers']);
            Route::get("account",[GeneralLedgerController::class,'ledger']);
        });

        Route::prefix("vouchers")->group( function(){
            Route::get("settings-data",[VouchersController::class,'settings_data']);
            Route::post("create",[VouchersController::class,'create']);
            Route::get("/",[VouchersController::class,'vouchers']);
            Route::get("voucher",[VouchersController::class,'voucher']);
            Route::post("edit",[VouchersController::class,'edit']);
            Route::post("delete",[VouchersController::class,'delete']);
        });

        Route::prefix("payments")->group( function(){
            Route::get("filter",[PaymentsController::class, 'filter']);
            Route::post("approve-selected",[PaymentsController::class, 'approveSelected']);
            Route::post("reject-selected",[PaymentsController::class, 'rejectSelected']);
            Route::post("dis-approve-selected",[PaymentsController::class, 'disApproveSelected']);
            Route::post("create-customer-withdraw",[PaymentsController::class,'create_customer_withdraw']);
            Route::post("update-customer-withdraw",[PaymentsController::class,'update_customer_withdraw']);
            Route::get("settings-data",[PaymentsController::class,'settings_data']);
            Route::post("create",[PaymentsController::class,'create']);
            Route::get("payment-withdraw",[PaymentsController::class, 'receiptWithdraw']);
            Route::post("create-payment-cheque",[PaymentsController::class,'create_payment_cheque']);
            Route::post("update-payment-cheque",[PaymentsController::class,'update_payment_cheque']);
            Route::get("/",[PaymentsController::class, 'payments']);
            Route::get("payment",[PaymentsController::class, 'payment']);
            Route::get("payment-multiple",[PaymentsController::class, 'paymentMultiple']);
            Route::get("payment-single",[PaymentsController::class, 'paymentSingle']);
            Route::get("payment-deposit",[PaymentsController::class, 'paymentDeposit']);
            Route::post("edit",[PaymentsController::class,'edit']);
            Route::post("delete",[PaymentsController::class,'delete']);
        });

        Route::prefix("receipts")->group( function(){

            Route::post("create-customer-deposit",[ReceiptsController::class,'create_customer_deposit']);
            Route::post("update-customer-deposit",[ReceiptsController::class,'update_customer_deposit']);
            Route::get("settings-data",[ReceiptsController::class,'settings_data']);
            Route::post("create",[ReceiptsController::class,'create']);
            Route::post("create-receipt-cheque",[ReceiptsController::class,'create_receipt_cheque']);
            Route::post("update-receipt-cheque",[ReceiptsController::class,'update_receipt_cheque']);
            Route::get("/",[ReceiptsController::class, 'receipts']);
            Route::get("filter",[ReceiptsController::class, 'filter']);
            Route::post("approve-selected",[ReceiptsController::class, 'approveSelected']);
            Route::post("reject-selected",[ReceiptsController::class, 'rejectSelected']);
            Route::post("dis-approve-selected",[ReceiptsController::class, 'disApproveSelected']);
            Route::get("receipt",[ReceiptsController::class, 'receipt']);
            Route::get("receipt-multiple",[ReceiptsController::class, 'receiptMultiple']);
            Route::get("receipt-single",[ReceiptsController::class, 'receiptSingle']);
            Route::post("edit",[ReceiptsController::class,'edit']);
            Route::post("delete",[ReceiptsController::class,'delete']);
        });

        Route::prefix("journals")->group( function(){
            Route::get("settings-data",[JournalsController::class,'settings_data']);
            Route::post("create",[JournalsController::class,'create']);
            Route::get("/",[JournalsController::class,'journals']);
            Route::get("journal",[JournalsController::class,'journal']);
            Route::post("edit",[JournalsController::class,'edit']);
            Route::post("delete",[JournalsController::class,'delete']);
        });

    });

    Route::prefix("transactions")->group( function(){
        Route::get("settings-data",[TransactionsController::class,'settings_data']);
        Route::get("filter",[TransactionsController::class, 'filter']);
        Route::post("approve-selected",[TransactionsController::class, 'approveSelected']);
        Route::post("reject-selected",[TransactionsController::class, 'rejectSelected']);
        Route::post("dis-approve-selected",[TransactionsController::class, 'disApproveSelected']);
        Route::get("transaction-data",[TransactionsController::class,'transaction']);
        Route::get("all-transactions",[TransactionsController::class, 'all_transactions']);
        Route::get("account-transactions",[TransactionsController::class, 'account_transactions']);
        Route::get("all-transactions-status",[TransactionsController::class, 'all_transactions_status']);
        Route::get("all-transactions-type-status",[TransactionsController::class, 'all_transactions_type_status']);
        Route::get("new-transactions",[TransactionsController::class, 'new_transactions']);
        Route::post("transaction-create",[TransactionsController::class, 'create_transaction']);
        Route::post("document-create",[TransactionsController::class, 'create_document']);
        Route::post("delete-file",[TransactionsController::class, 'delete_document']);
        Route::post("update",[TransactionsController::class, 'update_transaction']);
        Route::post("transaction-update-status",[TransactionsController::class, 'update_status']);
    });

    Route::prefix("hr")->group( function(){
        Route::prefix("employees")->group( function (){
            Route::get("activities",[EmployeesController::class,'user_activities']);
            Route::get("",[EmployeesController::class,'employees']);
            Route::post("create",[EmployeesController::class, 'create']);
            Route::post("edit",[EmployeesController::class, 'update']);
            Route::post("change-password",[EmployeesController::class, 'change_password']);
            Route::post("suspend",[EmployeesController::class, 'suspend']);
            Route::post("activate",[EmployeesController::class, 'activate']);
            Route::post("unsuspend",[EmployeesController::class, 'unsuspend']);
            Route::get("profile",[EmployeesController::class, 'profile']);
            Route::post("create-document",[EmployeesController::class, 'create_document']);
            Route::post("delete-document",[EmployeesController::class, 'delete_document']);
            Route::get("settings-data",[EmployeesController::class,'settings_data']);
            Route::post("change-account-social-data",[EmployeesController::class,'change_account_social_data']);
            Route::post("change-account-notification-data",[EmployeesController::class,'change_account_notification_data']);
            Route::post("change-account-mail-server",[EmployeesController::class,'change_account_mail_server']);
            Route::post("edit-role-permissions",[EmployeesController::class, 'update_roles_permissions']);
        });
    });

    Route::prefix("departments")->group( function(){
        Route::get("department-list",[DepartmentsController::class,'departments']);
        Route::post("department-create",[DepartmentsController::class,'create']);
        Route::post("department-edit",[DepartmentsController::class,'edit']);
        Route::post("department-delete",[DepartmentsController::class,'delete']);
    });

    Route::prefix("companies")->group( function(){
        Route::get("companies-list",[CompaniesController::class,'companies']);
        Route::get("sectors",[CompaniesController::class,'sectors']);
        Route::get("bonds-list",[CompaniesController::class,'bonds']);
        Route::post("company-create",[CompaniesController::class,'create']);
        Route::post("company-edit",[CompaniesController::class,'edit']);
        Route::post("bond-create",[CompaniesController::class,'create_bond']);
        Route::post("bond-edit",[CompaniesController::class,'edit_bond']);
    });


    Route::prefix("settings")->group( function (){

        Route::get("get-mailing-list",[SettingsController::class,'get_mailing_list']);
        Route::post("add-mailing-list",[SettingsController::class,'add_mailing_list']);
        Route::post("test-mail-delivery",[SettingsController::class,'test_email_delivery']);
        Route::post("send-predefined-email",[SettingsController::class,'send_predefine_email']);
        Route::post("update-mailing-list",[SettingsController::class,'update_mailing_list']);


        Route::get("business-data",[AccountController::class,'business_data']);
        Route::post("change-business-general-data",[AccountController::class,'change_business_general_data']);
        Route::post("update-sms-settings",[AccountController::class,'update_sms_settings']);
        Route::post("change-business-broker-data",[AccountController::class,'change_business_broker_data']);
        Route::post("change-business-logo",[AccountController::class,'change_business_logo']);
        Route::get("get-business-years",[AccountController::class,'get_business_years']);
        Route::post("delete-business-year",[AccountController::class,'delete_business_year']);
        Route::post("modify-business-year",[AccountController::class,'modify_business_year']);
        Route::post("add-business-year",[AccountController::class,'add_business_year']);

        Route::get("roles-data",[AccountController::class,'roles_data']);
        Route::get("roles-data-permissions",[AccountController::class,'roles_data_permission']);
        Route::get("roles-data-filter",[AccountController::class,'roles_data_filter']);
        Route::post("roles-add-role",[AccountController::class,'roles_add_role']);
        Route::post("roles-edit-role",[AccountController::class,'roles_edit_role']);
        Route::get("roles-data-delete",[AccountController::class,'roles_delete_role']);

        Route::get("account-data",[AccountController::class,'account_data']);
        Route::post("change-account-password",[AccountController::class,'change_password']);
        Route::post("change-account-general-data",[AccountController::class,'change_account_general_data']);
        Route::post("change-account-profile-picture",[AccountController::class,'change_account_profile_picture']);
        Route::post("change-account-cover-picture",[AccountController::class,'change_account_cover_picture']);
        Route::post("change-account-social-data",[AccountController::class,'change_account_social_data']);
        Route::post("change-account-notification-data",[AccountController::class,'change_account_notification_data']);
        Route::post("change-account-mail-server",[AccountController::class,'change_account_mail_server']);
    });

    Route::prefix("profile")->group( function (){
        Route::get("profile-data",[AccountController::class,'profile_data']);
        Route::get("profile-activities",[AccountController::class,'profile_activities']);
    });

    Route::get('me', function (){
        return  response()->json(new AdminProfile());
    });


});

Route::post('login', [AuthController::class,'login']);

Route::get('logout', [AuthController::class,'logout']);

Route::post('register', [AuthController::class,'register']);

Route::post('send-verify-account',[AuthController::class,'send_verification_link']);

Route::post('verify-account',[AuthController::class,'verify_account']);

Route::post('send-password-reset',[AuthController::class,'send_password_reset']);

Route::post('forgot-password',[AuthController::class,'send_password_reset']);

Route::post('password-reset',[AuthController::class,'password_reset']);

Route::get("kalenda", function (){

    $holidays[0]["2021"] =  ["HWHWWWWWWHWHWWWWHWWWWWWHWWWWWWH", "WWWWWWHWWWWWWHWWWWWWHWWWWWWH", "WWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW", "WHWHHWHWWWHWWWWWWHWWWWWWHHWWWW", "HHWWWWWWHWWWHHWHWWWWWWHWWWWWWHW", "WWWWWHWWWWWWHWWWWWWHWWWWWWHWWW", "WWWHWWHWWWHWWWWWWHWHHWWWHWWWWWW", "HWWWWWWHWWWWWWHWWWWWWHWWWWWWHWW", "WWWWHWWWWWWHWWWWWWHWWWWWWHWWWW", "WWHWWWWWWHWWWHWWHHHWWWWHWWWWWWH", "WWWWWWHWWWWWWHWWWWWWHWWWWWWHWW", "WWWWHWWWHWWHWWWWWWHWWWWWHHWWWWW"];
    $holidays[1]["2022"] =  ["HHWWWWWWHWWHWWWHWWWWWWHWWWWWWHW", "WWWWWHWWWWWWHWWWWWWHWWWWWWHW", "WWWWWHWWWWWWHWWWWWWHWWWWWWHWWWW", "WWHWWWWWWHWWWWHWHHWWWWWHWHWWWW", "HWHHWWWHWWWWWWHWWWWWWHWWWWWWHWW", "WWWWHWWWWWWHWWWWWWHWWWWWWHWWWW", "WWWWWWWWWHWWWWWWHWWWWWWHWWWWWWH", "WWWWWWHHWWWWWHWWWWWWHWWWWWWHWWW", "WWWHWWWWWWHWWWWWWHWWWWWWHWWWWW", "WHWWWWWWHWWWWWWHWWWWWWHWWWWWWHW", "WWWWWHWWWWWWHWWWWWWHWWWWWWHWWW", "WWWHWWWWHWHWWWWWWHWWWWWWHHWWWWW"];
    $holidays[2]["2023"] =  ["HWWWWWWHWWWHWWHWWWWWWHWWWWWWHWW", "WWWWHWWWWWWHWWWWWWHWWWWWWHWW", "WWWWHWWWWWWHWWWWWWHWWWWWWHWWWWW", "WHWWWWHWHHWWWWWHWWWWWHHWWHWWWH", "HWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW", "WWWHWWWWWWHWWWWWWHWWWWWWHWWWWW", "WHWWWWHWHWWWWWWHWWWWWWHWWWWWWHW", "WWWWWHWHWWWWHWWWWWWHWWWWWWHWWWW", "WWHWWWWWWHWWWWWWHWWWWWWHWWWHWW", "HWWWWWWHWWWWWHHWWWWWWHWWWWWWHWW", "WWWWHWWWWWWHWWWWWWHWWWWWWHWWWW", "WWHWWWWWHHWWWWWWHWWWWWWHHHWWWWH"];
    $holidays[3]["2024"] =  ["HWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW", "WWWHWWWWWWHWWWWWWHWWWWWWHWWWW", "WWHWWWWWWHWWWWWWHWWWWWWHWWWWWWH", "WWWWWWHWWWWWWHWWWWWWHWWWWWWHWW", "WWWWHWWWWWWHWWWWWWHWWWWWWHWWWWW", "WHWWWWWWHWWWWWWHWWWWWWHWWWWWWH", "WWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW", "WWWHWWWWWWHWWWWWWHWWWWWWHWWWWWW", "HWWWWWWHWWWWWWHWWWWWWHWWWWWWHW", "WWWWWHWWWWWWHWWWWWWHWWWWWWHWWWW", "WWHWWWWWWHWWWWWWHWWWWWWHWWWWWW", "HWWWWWWHWWWWWWHWWWWWWHWWWWWWHWW"];
    $holidays[4]["2025"] =  ["WWWWHWWWWWWHWWWWWWHWWWWWWHWWWWW", "WHWWWWWWHWWWWWWHWWWWWWHWWWWW", "WHWWWWWWHWWWWWWHWWWWWWHWWWWWWHW", "WWWWWHWWWWWWHWWWWWWHWWWWWWHWWW", "WWWHWWWWWWHWWWWWWHWWWWWWHWWWWWW", "WWWWWWWHWWWWWWHWWWWWWHWWWWWWHW", "WWWWWHWWWWWWHWWWWWWHWWWWWWHWWWW", "WWHWWWWWWHWWWWWWHWWWWWWHWWWWWWH", "WWWWWWHWWWWWWHWWWWWWHWWWWWWHWW", "WWWWHWWWWWWHWWWWWWHWWWWWWHWWWWW", "WHWWWWWWHWWWWWWHWWWWWWHWWWWWWH", "WWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW"];
    foreach ($holidays as   $years) {
        foreach ($years as $year => $months) {
            if(!empty($months)){
                $month = 0;
                foreach ($months as $month => $days){
                    $list = str_split($days);
                    if(!empty($list)){
                        $month = $month + 1;
                        foreach ($list  as $i =>$item){
//                            dd($item);
                            $day = $i + 1;
                            $date = $year."-".$month.'-'.$day;
                            $now = Carbon::createFromFormat("Y-m-d",$date);

                            $dateCheck = date("Y-m-d",strtotime($date));
                            $status = Calendar::where("today",$dateCheck)->first();
                            if(empty($status)){
                                $calendar = new Calendar();
                                $calendar->today = date("Y-m-d",strtotime($date));
                                $calendar->start = date("Y-m-d H:i:s",strtotime($date));
                                $calendar->end = date("Y-m-d H:i:s",strtotime($date));

                                $calendar->calendar = "Business";
                                $calendar->title = "Business";
                                $calendar->weekend = false;


                                if(strtolower($item) == "h"){
                                    $calendar->calendar = "Holiday";
                                    $calendar->title = "Holiday";
                                }

                                if($now->isSunday()){
                                    $calendar->calendar = "Weekend";
                                    $calendar->title = "Weekend";
                                    $calendar->weekend = true;
                                }

                                if($now->isSaturday()){
                                    $calendar->calendar = "Weekend";
                                    $calendar->title = "Weekend";
                                    $calendar->weekend = true;
                                }

                                $today = now(getenv("TIMEZONE"));
                                $dateCheck = Carbon::createFromFormat("Y-m-d",$date);
                                $dateCheck->addDay();
                                if($dateCheck->lessThan($today)){
                                    $calendar->closed = true;
                                }else{
                                    $calendar->closed = false;
                                }

                                $calendar->save();
                                echo $dateCheck. "<br/> ".$calendar->calendar;
                            }
                        }
                    }
                }
            }

        }


    }
});
