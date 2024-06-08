<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {

//        $file_path = resource_path('data/migrations/kelvin_bboo.sql');
//
//        \DB::unprepared(
//            file_get_contents($file_path)
//        );
//


//          $this->call(BusinessSeeder::class);
//          $this->call(FinancialYearsSeeder::class);
//          $this->call(PaymentMethodsSeeder::class);
//          $this->call(AccountingSeeder::class);
//          $this->call(AccountCategoriesSeeder::class);
//          $this->call(AccountSettingsSeeder::class);
//          $this->call(BanksSeeder::class);
//          $this->call(SectorsSeeder::class);
//          $this->call(UsersSeeder::class);
//          $this->call(TradingFeesPayeeSeeder::class);
//          $this->call(SecuritiesSeeder::class);
         $this->call(BondsSeeder::class);
//          $this->call(SchemesSeeder::class);
//          $this->call(CustomerCategoriesSeeder::class);
//          $this->call(CustomersSeeder::class);
//          $this->call(PayeesSeeder::class);
//          $this->call(HolidaysSeeder::class);
//          $this->call(ModulesTableSeeder::class);
//          $this->call(WhatsappSettingsSeeder::class);
//          $this->call(SmsSettingsSeeder::class);
//          $this->call(CountriesSeeder::class);
//          $this->call(DseSettingsSeeder::class);
//          $this->call(RefreshOrdersSeeder::class);
//          $this->call(RefreshTelescopeSeeder::class);
//          $this->call(RefreshPulseSeeder::class);

//         DB::unprepared("
//                              ALTER TABLE
//                         `statements` ADD `auto` BIGINT NOT NULL AUTO_INCREMENT AFTER `id`,
//                         ADD INDEX(`auto`);
//                     ALTER TABLE
//                         `transactions` ADD `auto` BIGINT NOT NULL AUTO_INCREMENT AFTER `id`,
//                         ADD INDEX(`auto`);
//                     ALTER TABLE
//                         `orders` ADD `auto` BIGINT NOT NULL AUTO_INCREMENT AFTER `id`,
//                         ADD INDEX(`auto`);
//                     ALTER TABLE
//                         `bond_orders` ADD `auto` BIGINT NOT NULL AUTO_INCREMENT AFTER `id`,
//                         ADD INDEX(`auto`);
//                     ALTER TABLE
//                         `dealing_sheets` ADD `auto` BIGINT NOT NULL AUTO_INCREMENT AFTER `id`,
//                         ADD INDEX(`auto`);
//                     ALTER TABLE
//                         `bond_executions` ADD `auto` BIGINT NOT NULL AUTO_INCREMENT AFTER `id`,
//                         ADD INDEX(`auto`);
//                     ALTER TABLE
//                         `users` ADD `auto` BIGINT NOT NULL AUTO_INCREMENT AFTER `id`,
//                         ADD INDEX(`auto`);
//          ");
//         $user = User::factory()
//             ->count(1000)
//             ->has(
//                 Profile::factory()
//                     ->count(1)
//                     ->state(function (array $attributes, User $user) {
//                         return [
//                             'user_id' => $user->id,
//                             'firstname' => $user->firstname,
//                             'lastname' => $user->lastname,
//                             'name' => $user->name,
//                             'mobile' => $user->mobile,
//                             'email' => $user->email
//                         ];
//                     })
//             )
//             ->create();
    }
}

