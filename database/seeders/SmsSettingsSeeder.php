<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SMS\Entities\SmsSetting;
use Modules\SMS\Helpers\SmsFunctions;

class SmsSettingsSeeder extends Seeder
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
        DB::table('sms_settings')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function data(): void
    {
        $setting = SmsSetting::firstOrCreate(['sms_provider' => 'beem']);
        $setting->sms_base_url = 'https://apisms.beem.africa/v1';
        $setting->sms_api_key = '018cc1fd86ba82c6';
        $setting->sms_secret_key = 'YTQ5MDBiZmFhZDhiZDkxZTdmNDQ0M2I0OTBlN2E3MWM1YjQ3ZjNhYjg3NzBjNGQ0MDhmNTRlMjU3MDgzYjIyYw==';
        $setting->sms_sender_id = 'iTrust';
        $setting->save();
        SmsFunctions::send_sms(recipients: ['255746251394'], message: 'This is sample text message number '.random_int(11111, 99999));
    }
}
