<?php

namespace Modules\DSE\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DSE\Entities\DSESettings;

class DSESettingsSeeder extends Seeder
{
    public function run(): void
    {
        $status = DSESettings::first();
        if (empty($status)) {
            $settings = new DSESettings();
            $settings->timeout = 100000;
            $settings->nida = '19870427141240000127';
            $settings->broker_reference = 'e9ae431ffc7348f0802b5da44a9f67fc';
            $settings->username = 'ict@itrust.co.tz';
            $settings->password = '8H9jx7hJ';
            $settings->grant_type = 'password';
            $settings->client_id = 'ITRUST';
            $settings->client_secret = 'ITRUST@2024';
            $settings->encoded_token = base64_encode('ITRUST:ITRUST@2024');
            $settings->base_url = '192.168.1.25:7080';
            $settings->access_token = '6239d0d0-7091-4098-90de-027e1a0d721d';
            $settings->refresh_token = '7107d4e0-23a6-4f27-9100-9050b048f0a2';
            $settings->scope_in = 'read write';
            $settings->signature = '';
            $settings->save();
        }
    }
}
