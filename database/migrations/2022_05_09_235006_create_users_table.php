<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index()->nullable();
            $table->string('status')->default('pending');
            $table->string('type')->index()->nullable();
            $table->string('timezone')->default('Africa/Dar_es_Salaam');
            $table->string('email')->nullable()->unique('users_email_unique');
            $table->string('mobile')->nullable();
            $table->boolean('self_registration')->default(false);
            $table->boolean('is_admin')->index()->default(false);
            $table->string('has_custodian')->default('no');
            $table->string('custodian_approved')->default('no');
            $table->string('dse_account')->nullable();
            $table->string('custodian')->nullable();
            $table->string('bot_account')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('subscription')->nullable();
            $table->string('subscription_email')->nullable();
            $table->string('subscription_phone')->nullable();
            $table->string('manager_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('otp_time');
            $table->string('otp_code');
            $table->string('otp_verified');
            $table->string('ip_address')->nullable();
            $table->text('verify_token')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->foreignUuid('category_id')->nullable();
            $table->text('token')->nullable();
            $table->text('device_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
}
