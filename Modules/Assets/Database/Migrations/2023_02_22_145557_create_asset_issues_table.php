<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('description')->nullable();
            $table->text('comments')->nullable();
            $table->date('expected_fix_date')->nullable();
            $table->date('resolved_date')->nullable();
            $table->enum('status', ['New', 'In Progress', 'Resolved', 'Blocker', 'Pending', 'Hold', 'Rejected', 'Accepted', 'Closed'])->default('New');
            $table->uuid('asset_id')->nullable();
            $table->uuid('raised_by')->nullable();
            $table->uuid('approved_by')->nullable();
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
        Schema::dropIfExists('asset_issues');
    }
}
