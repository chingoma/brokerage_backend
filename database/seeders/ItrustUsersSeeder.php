<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class ItrustUsersSeeder extends Seeder
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
            $users = [
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Abdul Bandawe',
                    'status' => 'active',
                    'email' => 'abdul@itrust.co.tz',
                    'mobile' => '255746000001',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Abdul',
                    'lastname' => 'Bandawe',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Zubeda Salim',
                    'status' => 'active',
                    'email' => 'zubeda@itrust.co.tz',
                    'mobile' => '255746000002',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Zubeda Salim',
                    'lastname' => 'Inputer',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Frank Bunuma',
                    'status' => 'active',
                    'email' => 'frank@itrust.co.tz',
                    'mobile' => '255746000003',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Frank Bunuma',
                    'lastname' => 'Auth',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Francis Samkyi',
                    'status' => 'active',
                    'email' => 'francis@itrust.co.tz',
                    'mobile' => '255746000004',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Francis',
                    'lastname' => 'Samkyi',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Salim Manji',
                    'status' => 'active',
                    'email' => 'salim@imaan.co.tz',
                    'mobile' => '255746000005',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Salim',
                    'lastname' => 'Manji',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Antony Elias',
                    'status' => 'active',
                    'email' => 'antony.elias@itrust.co.tz',
                    'mobile' => '255746000006',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Antony',
                    'lastname' => 'Elias',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Faiz Arab',
                    'status' => 'active',
                    'email' => 'faiz@itrust.co.tz',
                    'mobile' => '255746000006',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('123456789'),
                    'firstname' => 'Faiz',
                    'lastname' => 'Arab',
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

    /**
     * Truncates  table
     */
    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
