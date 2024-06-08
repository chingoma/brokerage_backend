<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Whatsapp\Entities\WhatsappSetting;
use Modules\Whatsapp\Helpers\WhatsappMessagesHelper;
use Ramsey\Uuid\Uuid;

class WhatsappSettingsSeeder extends Seeder
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
        DB::table('whatsapp_settings')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function data(): void
    {
        $settings = WhatsappSetting::first();
        if (empty($settings)) {
            $settings = new WhatsappSetting();
        }
        $settings->whatsapp_id = '207535419100167';
        $settings->w_business_id = '142938538913796';
        $settings->base_url = 'https://graph.facebook.com';
        $settings->access_token = 'EAAE772E9p94BO7taAyZAw6yk4vKzhd1ryBRVKGVNG5xeclu2pcZBmyfCFZBvYBIEfEK2cwZB2mkALrlEI5jwKoiqKGcoY5mmnH46tOiJbttSi24v8OiE3SsBfC8sm5ZBrRqZCIOPgyZBbhZAM5v3vEgL0ZAZAgibcpZBK0DCXsBTLNIeIaH7Ip8MriPEqBeQ6URMo9UEinSmvIp5M9BIfvp';
        $settings->webhook_token = base64_encode(Uuid::uuid7());
        $settings->callback_url = 'https://admin-api.brokerlick.co.tz/whatsapp/callback';
        $settings->status = 'active';
        $settings->api_version = 'v19.0';
        $settings->save();
        WhatsappMessagesHelper::sendHelloWorld(recipient: '255746251394');

    }
}
