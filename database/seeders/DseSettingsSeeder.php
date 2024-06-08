<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\DSE\Entities\DSESettings;
use Modules\Whatsapp\Entities\WhatsappSetting;
use Modules\Whatsapp\Helpers\WhatsappMessagesHelper;
use Ramsey\Uuid\Uuid;

class DseSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTables();

        try {
            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();
            $this->data();
            DB::commit();
            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->command->error($throwable->getMessage());
            exit();
        }

    }

    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('dse_settings')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function data(): void
    {
        $settings = DSESettings::first();
        if (empty($settings)) {
            $settings = new DSESettings();
        }
        $settings->encoded_token = 'SVRSVVNUOjEyMzQ1Njc4';
        $settings->timeout = 25;
        $settings->broker_reference = 'e9ae431ffc7348f0802b5da44a9f67fc';
        $settings->nida = '19870427141240000127';
        $settings->username = 'ict@itrust.co.tz';
        $settings->password = "8H9jx7hJ";
        $settings->grant_type = 'password';
        $settings->client_id = '';
        $settings->client_secret = '';
        $settings->base_url = '192.168.1.25:7080';
        $settings->access_token = '7bff37d8-097d-43c9-a4b6-34e8310d81d4';
        $settings->refresh_token = '6df78f72-79aa-4092-9a32-9a08189efd57';
        $settings->signature = '';
        $settings->expires_in = '';
        $settings->scope_in = 'read write';
        $settings->save();

    }
}
