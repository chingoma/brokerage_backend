<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\Bank;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\CRM\Entities\CustomerCategory;
use Ramsey\Uuid\Uuid;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        try {
            Schema::disableForeignKeyConstraints();
            DB::table('profiles')->truncate();
            DB::table('user_ids')->truncate();
            $bank = Bank::first();
            $category = CustomerCategory::where('default', 'yes')->first();
            $users = [
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Individual Customer',
                    'status' => 'active',
                    'email' => 'individual.customer@brokerlink.co.tz',
                    'type' => 'individual',
                    'mobile' => '255746000011',
                    'is_admin' => false,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Individual',
                    'lastname' => 'Customer',
                    'dse_account' => 1234567,
                    'self_registration' => false,
                    'bank_id' => $bank->id,
                    'bank_account_name' => 'Individual Customer',
                    'bank_account_number' => '1243549000',
                    'bank_branch' => 'Lockminds Branch',
                    'manager_id' => $category->manager_id,
                    'category_id' => $category->id,
                    'custodian_approved' => 'no',
                    'has_custodian' => 'no',
                ],
            ];
            foreach ($users as $user) {
                $user = User::create($user);
                Helper::customerUID($user);
                $profile = new Profile();
                $profile->region = 'Dar es salaam';
                $profile->district = 'Kinondoni';
                $profile->ward = 'Kinondoni';
                $profile->place_birth = 'Kinondoni';
                $profile->title = 'Mr.';
                $profile->firstname = $user->firstname;
                $profile->lastname = $user->lastname;
                $profile->name = 'Individual Customer';
                $profile->gender = 'Male';
                $profile->dob = '27-04-19987';
                $profile->identity = '214354657689';
                $profile->country_id = '221';
                $profile->nationality = 'Tanzanian';
                $profile->address = 'Goba, Mbezi';
                $profile->mobile = '255746251394';
                $profile->email = 'individual.customer@brokerlink.co.tz';
                $profile->tin = '999999999';
                $profile->user_id = $user->id;
                $profile->save();
            }
            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $throwable) {
            $this->command->error($throwable->getMessage());
            exit();
        }

    }
}
