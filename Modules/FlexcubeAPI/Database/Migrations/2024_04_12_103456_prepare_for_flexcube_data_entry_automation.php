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
        Schema::table("simple_transactions",function (Blueprint $table){
            $table->datetime("flexcube_post_date")->nullable()->index();
            $table->string("flexcube_reference")->nullable()->index();
            $table->string("flexcube_batch_no")->nullable()->index();
            $table->string("flexcube_sync_response")->nullable();
            $table->string("flexcube_status")->default("pending")->index();
            $table->uuid("flexcube_synced_by")->nullable()->index();
            $table->timestampTz("flexcube_synced_at")->nullable()->index();
        });
        Schema::table("transactions",function (Blueprint $table){
            $table->datetime("flexcube_post_date")->nullable()->index();
            $table->string("flexcube_reference")->nullable()->index();
            $table->string("flexcube_batch_no")->nullable()->index();
            $table->string("flexcube_status")->default("pending")->index();
            $table->string("flexcube_sync_response")->nullable();
            $table->uuid("flexcube_synced_by")->nullable()->index();
            $table->timestampTz("flexcube_synced_at")->nullable()->index();
        });
        Schema::table("payments",function (Blueprint $table){
            $table->datetime("flexcube_post_date")->nullable()->index();
            $table->string("flexcube_reference")->nullable()->index();
            $table->string("flexcube_batch_no")->nullable()->index();
            $table->string("flexcube_status")->default("pending")->index();
            $table->string("flexcube_sync_response")->nullable();
            $table->uuid("flexcube_synced_by")->nullable()->index();
            $table->timestampTz("flexcube_synced_at")->nullable()->index();
        });
        Schema::table("receipts",function (Blueprint $table){
            $table->datetime("flexcube_post_date")->nullable()->index();
            $table->string("flexcube_reference")->nullable()->index();
            $table->string("flexcube_batch_no")->nullable()->index();
            $table->string("flexcube_status")->default("pending")->index();
            $table->string("flexcube_sync_response")->nullable();
            $table->uuid("flexcube_synced_by")->nullable()->index();
            $table->timestampTz("flexcube_synced_at")->nullable()->index();
        });
        Schema::table("statements",function (Blueprint $table){
            $table->datetime("flexcube_post_date")->nullable()->index();
            $table->string("flexcube_reference")->nullable()->index();
            $table->string("flexcube_batch_no")->nullable()->index();
            $table->string("flexcube_status")->default("pending")->index();
            $table->string("flexcube_sync_response")->nullable();
            $table->uuid("flexcube_synced_by")->nullable()->index();
            $table->timestampTz("flexcube_synced_at")->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("simple_transactions",function (Blueprint $table){
            $table->dropColumn(["flexcube_post_date","flexcube_reference","flexcube_batch_no","flexcube_status","flexcube_synced_by","flexcube_synced_at","flexcube_sync_response"]);
        });
        Schema::table("transactions",function (Blueprint $table){
            $table->dropColumn(["flexcube_post_date","flexcube_reference","flexcube_batch_no","flexcube_status","flexcube_synced_by","flexcube_synced_at","flexcube_sync_response"]);
        });
        Schema::table("payments",function (Blueprint $table){
            $table->dropColumn(["flexcube_post_date","flexcube_reference","flexcube_batch_no","flexcube_status","flexcube_synced_by","flexcube_synced_at","flexcube_sync_response"]);
        });
        Schema::table("receipts",function (Blueprint $table){
            $table->dropColumn(["flexcube_post_date","flexcube_reference","flexcube_batch_no","flexcube_status","flexcube_synced_by","flexcube_synced_at","flexcube_sync_response"]);
        });
        Schema::table("statements",function (Blueprint $table){
            $table->dropColumn(["flexcube_post_date","flexcube_reference","flexcube_batch_no","flexcube_status","flexcube_synced_by","flexcube_synced_at","flexcube_sync_response"]);
        });
    }
};
