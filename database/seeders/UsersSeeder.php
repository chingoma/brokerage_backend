<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class UsersSeeder extends Seeder
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
                    'name' => 'Super User',
                    'status' => 'active',
                    'email' => 'super.user@brokerlink.co.tz',
                    'mobile' => '255746000001',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('Alvin2009!'),
                    'firstname' => 'Super',
                    'lastname' => 'User',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Trade Inputer',
                    'status' => 'active',
                    'email' => 'trade.inputer@brokerlink.co.tz',
                    'mobile' => '255746000002',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('Alvin2009!'),
                    'firstname' => 'Trade',
                    'lastname' => 'Inputer',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Trade Auth',
                    'status' => 'active',
                    'email' => 'trade.auth@brokerlink.co.tz',
                    'mobile' => '255746000003',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('Alvin2009!'),
                    'firstname' => 'Trade',
                    'lastname' => 'Auth',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Finance Inputer',
                    'status' => 'active',
                    'email' => 'finance.inputer@brokerlink.co.tz',
                    'mobile' => '255746000004',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('Alvin2009!'),
                    'firstname' => 'Finance',
                    'lastname' => 'Inputer',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Finance Auth',
                    'status' => 'active',
                    'email' => 'finance.auth@brokerlink.co.tz',
                    'mobile' => '255746000005',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('Alvin2009!'),
                    'firstname' => 'Finance',
                    'lastname' => 'Auth',
                ],
                [
                    'id' => Uuid::uuid7(),
                    'name' => 'Chief Officer',
                    'status' => 'active',
                    'email' => 'chief.officer@brokerlink.co.tz',
                    'mobile' => '255746000006',
                    'is_admin' => true,
                    'email_verified_at' => now()->timestamp,
                    'password' => \Hash::make('Alvin2009!'),
                    'firstname' => 'Chief',
                    'lastname' => 'Officer',
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
     * Truncates all the laratrust tables and the users table
     */
    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
