<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('uid')->nullable();
            $table->string('model');
            $table->string('serial');
            $table->text('image')->nullable();
            $table->string('description', 255)->nullable();
            $table->float('price')->default(0.0);
            $table->date('date_of_purchase');
            $table->date('date_of_manufacture');
            $table->string('location')->nullable();
            $table->longText('qr_code')->nullable();
            $table->json('employees')->nullable();
            $table->longText('barcode');
            $table->enum('status', ['New', 'In Use', 'Available', 'Damage', 'Return', 'Expired', 'Required License Update', 'Miscellaneous'])->default('New');
            $table->uuid('category_id')->nullable();
            $table->uuid('sub_category_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('supplier_id')->nullable();
            $table->uuid('debit_account')->nullable();
            $table->uuid('credit_account')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->uuid('business_id')->nullable();
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
        Schema::dropIfExists('assets');
    }
}
