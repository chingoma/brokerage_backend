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
        Schema::create('sms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->enum('provider', ['beem']);
            $table->string('recipient');
            $table->string('request_id');
            $table->string('source_addr');
            $table->timestamp('schedule_time')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->string('encoding')->default(0);
            $table->text('message');
            $table->string('status')->default('submitted');
            $table->string('response_message')->nullable();
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
        Schema::dropIfExists('sms');
    }
};
