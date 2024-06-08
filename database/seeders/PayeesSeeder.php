<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\CRM\Entities\CustomerCategory;

class PayeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        try {
            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();
            $bank = Bank::first();
            $category = CustomerCategory::where('default', 'yes')->first();
            $users = [
                [
                    'id' => '2587f26b-5ed2-4085-b7a5-e9bc0eaecb84',
                    'name' => 'CSMA Payee',
                    'status' => 'active',
                    'email' => 'csma-payee@brokerlink.co.tz',
                    'type' => 'payee',
                    'mobile' => '255746000031',
                    'is_admin' => false,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'CSMA',
                    'lastname' => 'Payee',
                    'dse_account' => 1234567,
                    'self_registration' => false,
                    'bank_id' => $bank->id,
                    'bank_account_name' => 'CSMA Payee',
                    'bank_account_number' => '1243549000',
                    'bank_branch' => 'Lockminds Branch',
                    'manager_id' => $category->manager_id,
                    'category_id' => $category->id,
                    'custodian_approved' => 'no',
                    'has_custodian' => 'no',
                ],
                [
                    'id' => '332cf872-dc01-42fe-b84b-2c65ecba05fa',
                    'name' => 'Fidelity Payee',
                    'status' => 'active',
                    'email' => 'fidelity-payee@brokerlink.co.tz',
                    'type' => 'payee',
                    'mobile' => '255746000031',
                    'is_admin' => false,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Fidelity',
                    'lastname' => 'Payee',
                    'dse_account' => 1234567,
                    'self_registration' => false,
                    'bank_id' => $bank->id,
                    'bank_account_name' => 'Fidelity Payee',
                    'bank_account_number' => '1243549000',
                    'bank_branch' => 'Lockminds Branch',
                    'manager_id' => $category->manager_id,
                    'category_id' => $category->id,
                    'custodian_approved' => 'no',
                    'has_custodian' => 'no',
                ],
                [
                    'id' => '6db73d68-c3c4-4450-bcf1-519dd475d0c1',
                    'name' => 'DSE Payee',
                    'status' => 'active',
                    'email' => 'dse-payee@brokerlink.co.tz',
                    'type' => 'payee',
                    'mobile' => '255746000031',
                    'is_admin' => false,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'DSE',
                    'lastname' => 'Payee',
                    'dse_account' => 1234567,
                    'self_registration' => false,
                    'bank_id' => $bank->id,
                    'bank_account_name' => 'DSE Payee',
                    'bank_account_number' => '1243549000',
                    'bank_branch' => 'Lockminds Branch',
                    'manager_id' => $category->manager_id,
                    'category_id' => $category->id,
                    'custodian_approved' => 'no',
                    'has_custodian' => 'no',
                ],
                [
                    'id' => '6db73d68-c3c4-4450-bcf1-519dd475d0cc',
                    'name' => 'CSDR Payee',
                    'status' => 'active',
                    'email' => 'csdr-payee@brokerlink.co.tz',
                    'type' => 'payee',
                    'mobile' => '255746000031',
                    'is_admin' => false,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'CSDR',
                    'lastname' => 'Payee',
                    'dse_account' => 1234567,
                    'self_registration' => false,
                    'bank_id' => $bank->id,
                    'bank_account_name' => 'CSDR Payee',
                    'bank_account_number' => '1243549000',
                    'bank_branch' => 'Lockminds Branch',
                    'manager_id' => $category->manager_id,
                    'category_id' => $category->id,
                    'custodian_approved' => 'no',
                    'has_custodian' => 'no',
                ],
                [
                    'id' => '63242358-7b0d-41cd-9864-2ac1b1dac7a7',
                    'name' => 'VAT Payee',
                    'status' => 'active',
                    'email' => 'vat-payee@brokerlink.co.tz',
                    'type' => 'payee',
                    'mobile' => '255746000031',
                    'is_admin' => false,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'VAT',
                    'lastname' => 'Payee',
                    'dse_account' => 1234567,
                    'self_registration' => false,
                    'bank_id' => $bank->id,
                    'bank_account_name' => 'VAT Payee',
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
            }
            DB::commit();
            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->command->error($throwable->getMessage());
            exit();
        }

    }
}
