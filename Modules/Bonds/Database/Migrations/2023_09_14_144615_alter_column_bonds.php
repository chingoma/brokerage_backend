<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonds', function (Blueprint $table) {
            $table->string('type');
            $table->renameColumn('bond_number', 'number');
            $table->renameColumn('bond_isin', 'isin');
            $table->renameColumn('bond_coupon', 'coupon');
            $table->renameColumn('bond_tenure', 'tenure');
            $table->renameColumn('bond_issue_date', 'issue_date');
            $table->renameColumn('bond_maturity_date', 'maturity_date');
            $table->renameColumn('bond_issued_amount', 'issued_amount');
        });

        Schema::table('account_settings', function (Blueprint $table) {
            $table->string('bond_account');
        });

        Schema::table('bond_executions', function (Blueprint $table) {
            //            $table->string("face_value");
            $table->string('other_charges');
            $table->string('bond_type');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('bond_executions', ['bond_type', 'face_value', 'other_charges']);
        Schema::dropColumns('bonds', ['type']);
        Schema::dropColumns('account_settings', ['bond_account']);
        Schema::table('bonds', function (Blueprint $table) {
            $table->renameColumn('number', 'bond_number');
            $table->renameColumn('isin', 'bond_isin');
            $table->renameColumn('coupon', 'bond_coupon');
            $table->renameColumn('tenure', 'bond_tenure');
            $table->renameColumn('issue_date', 'bond_issue_date');
            $table->renameColumn('maturity_date', 'bond_maturity_date');
            $table->renameColumn('issued_amount', 'bond_issued_amount');
        });
    }
};
