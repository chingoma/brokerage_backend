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

        Schema::table('bond_executions', function (Blueprint $table) {
            $table->string('commission_step_one');
            $table->string('commission_step_two');
            //            $table->string('uid');
            $table->uuid('order_id');
            $table->uuid('updated_by');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('bond_executions', ['commission_step_one', 'commission_step_two', 'order_id', 'uid', 'updated_by']);
    }
};
