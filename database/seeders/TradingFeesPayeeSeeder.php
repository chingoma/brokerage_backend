<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TradingFeesPayeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();
            $users = [
                [
                    'id' => '127',
                    'name' => 'DSE Fee',
                    'status' => 'active',
                    'email' => 'dse.fee@brokerlink.co.tz',
                    'mobile' => '255746000021',
                    'type' => 'fees',
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Dse',
                    'lastname' => 'Fees',
                ],
                [
                    'id' => '128',
                    'name' => 'CSDR Fee',
                    'status' => 'active',
                    'email' => 'csdr.fee@brokerlink.co.tz',
                    'mobile' => '255746000022',
                    'type' => 'fees',
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'CSDR',
                    'lastname' => 'Fees',
                ],
                [
                    'id' => '139',
                    'name' => 'VAT Fee',
                    'status' => 'active',
                    'email' => 'vat.fee@brokerlink.co.tz',
                    'mobile' => '255746000023',
                    'type' => 'fees',
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'VAT',
                    'lastname' => 'Fees',
                ],
                [
                    'id' => '126',
                    'name' => 'FIDELITY Fee',
                    'status' => 'active',
                    'email' => 'fidelity.fee@brokerlink.co.tz',
                    'mobile' => '255746000024',
                    'type' => 'fees',
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'FIDELITY',
                    'lastname' => 'Fees',
                ],
                [
                    'id' => '125',
                    'name' => 'CSMA Fee',
                    'status' => 'active',
                    'email' => 'csma.fee@brokerlink.co.tz',
                    'mobile' => '255746000024',
                    'type' => 'fees',
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'CSMA',
                    'lastname' => 'Fees',
                ],
            ];
            foreach ($users as $user) {
                User::create($user);
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
