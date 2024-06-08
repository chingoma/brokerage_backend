<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flexcube_postings', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("entity_id");
            $table->enum("category",["PROBOOK STOCK","PROBOOK BOND","CUSTODIAN BOND","CUSTODIAN PROBOOK STOCK","CUSTODIAN PROBOOK BOND","CUSTODIAN STOCK","STOCK","BOND","RECEIPT","PAYMENT"]);
            $table->enum("type",["DEPOSIT","WITHDRAW","SALE","PURCHASE"]);
            $table->enum("bond_type",["PRIMARY","SECONDARY"])->nullable();
            $table->string("tran_reference")->index();
            $table->string("main_action");
            $table->string("batch_no");
            $table->text("description")->nullable();
            $table->date("tran_date")->index();
            $table->date("settlement_date")->index();
            $table->date("value_date")->index();
            $table->decimal("quantity",12,2)->default(0);
            $table->decimal("price");
            $table->decimal("consideration",12,2)->default(0);
            $table->string("consideration_account")->nullable();

            $table->decimal("total_charges",12,2)->default(0);

            $table->decimal("brokerage",12,2)->default(0);
            $table->string("brokerage_account")->nullable();

            $table->decimal("vat",8,2)->default(0);
            $table->string("vat_account")->nullable();

            $table->decimal("dse",8,2)->default(0);
            $table->string("dse_account")->nullable();

            $table->decimal("cds",8,2)->default(0);
            $table->string("cds_account")->nullable();

            $table->decimal("fidelity",8,2)->default(0);
            $table->string("fidelity_account")->nullable();

            $table->decimal("cmsa",8,2)->default(0);
            $table->string("cmsa_account")->nullable();

            $table->decimal("other_charges",8,2)->default(0);
            $table->string("other_charges_account")->nullable();

            $table->decimal("payout",12,2)->default(0);

            $table->decimal("debit",12,2)->default(0);
            $table->string("debit_account")->nullable();

            $table->decimal("credit",12,2)->default(0);
            $table->string("credit_account")->nullable();

            $table->decimal("debit_credit_diff",12,2)->default(0);

            $table->softDeletesTz();
            $table->timestampsTz();
            $table->unique(["entity_id","tran_reference"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flexcube_postings');
    }
};
