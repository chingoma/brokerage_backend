<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BusinessSeeder extends Seeder
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
            $this->business();
            DB::commit();
            Schema::enableForeignKeyConstraints();
        } catch (Throwable $throwable) {
            DB::rollBack();
            $this->command->error($throwable->getMessage());
            exit();
        }

    }

    /**
     * Truncates all  tables and the users table
     */
    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('businesses')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function business(): void
    {
        $businesses = [
            ['id' => 'e1440a12-5445-4fba-aa8f-fcd24f7aff20', 'administrator' => null, 'name' => getenv("APP_NAME"), 'facebook' => '', 'twitter' => '', 'google' => '', 'quora' => '', 'instagram' => '', 'linkedin' => '', 'logo' => null, 'country' => null, 'address' => 'P.O.BOX 104489DAR ES SALAAM', 'telephone' => '255746251394', 'fax' => '', 'email' => 'info@lockminds.com', 'website' => 'https://lockminds.com', 'terms_of_use_url' => null, 'privacy_url' => null, 'about' => '', 'year_start' => null, 'year_end' => null, 'financial_year' => '208aebe4-1d33-11ee-925c-e14ddc666dee', 'created_at' => '2023-07-07 05:51:42', 'updated_at' => '2023-10-24 05:55:21', 'deleted_at' => null, 'timezone' => 'Africa/Dar_es_Salaam', 'sms_api_key' => '018cc1fd86ba82c6', 'sms_secret_key' => 'YTQ5MDBiZmFhZDhiZDkxZTdmNDQ0M2I0OTBlN2E3MWM1YjQ3ZjNhYjg3NzBjNGQ0MDhmNTRlMjU3MDgzYjIyYw==', 'sms_sender_id' => 'iTrust', 'mail_host' => null, 'mail_port' => null, 'mail_username' => null, 'mail_password' => null, 'mail_encryption' => null, 'mail_from_address' => null, 'mail_from_name' => null, 'otp_enabled' => 'yes', 'sms_otp_enabled' => 'no', 'notification_emails' => null, 'send_client_notifications' => null],
        ];
        foreach ($businesses as $business) {
            Business::create($business);
        }
    }
}
