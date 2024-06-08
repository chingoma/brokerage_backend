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
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('sms_api_key')->nullable();
            $table->string('sms_secret_key')->nullable();
            $table->string('sms_sender_id')->nullable();
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->string('otp_enabled')->default('yes')->nullable();
            $table->string('sms_otp_enabled')->default('no')->nullable();
            $table->string('notification_emails')->nullable();
            $table->string('send_client_notifications')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('businesses', [
            'sms_api_key',
            'sms_secret_key',
            'sms_sender_id',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
            'otp_enabled',
            'notification_emails',
            'sms_otp_enabled',
            'send_client_notifications']);

    }
};
