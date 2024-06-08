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
        Schema::create('flexcube_transactions', function (Blueprint $table) {
            $table->uuid();
            $table->string("batch_no")->index();
            $table->string("trn_ref_no")->index();
            $table->string("value_dt")->index();
            $table->string("datetime")->index();
            $table->string("ac_ccy")->default("TZS");
            $table->string("ac_branch")->index();
            $table->string("ac_no")->index();
            $table->string("ac_gl_desc")->nullable();
            $table->string("addl_text");
            $table->string("de_description");
            $table->string("event")->default("INIT");
            $table->string("module")->default("DE");
            $table->string("instrument_code");
            $table->string("debits");
            $table->string("credits");
            $table->uuid("syncled_by");
            $table->dateTimeTz("syncled_at");
            $table->softDeletes();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flexcube_transactions');
    }
};
