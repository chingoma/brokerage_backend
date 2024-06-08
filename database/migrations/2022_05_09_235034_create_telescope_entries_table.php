<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelescopeEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid('uuid')->unique('telescope_entries_uuid_unique');
            $table->uuid('batch_id')->index('telescope_entries_batch_id_index');
            $table->string('family_hash')->nullable()->index('telescope_entries_family_hash_index');
            $table->boolean('should_display_on_index')->default(1);
            $table->string('type', 20);
            $table->longText('content');
            $table->dateTime('created_at')->nullable()->index('telescope_entries_created_at_index');
            $table->index(['type', 'should_display_on_index'], 'telescope_entries_type_should_display_on_index_index');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telescope_entries');
    }
}
