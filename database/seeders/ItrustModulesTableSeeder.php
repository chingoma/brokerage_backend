<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ItrustModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): bool
    {
        $this->truncateLaratrustTables();

        $config = Config::get('permissions_seeder.roles_structure');

        if ($config === null) {
            $this->command->error('The configuration has not been published. Did you run `php artisan vendor:publish --tag="laratrust-seeder"`');
            $this->command->line('');

            return false;
        }

        $mapPermission = collect(config('permissions_seeder.permissions_map'));

        foreach ($config as $key => $modules) {

            // Create a new role
            $role = new Role();
            $role->name = $key;
            $role->guard_name = 'web';
            $role->save();

            $this->command->info('Creating Role '.strtoupper($key));

            // Reading role permission modules
            foreach ($modules as $module => $value) {

                Module::firstOrCreate(['name' => $module]);

                foreach (explode(',', $value) as $p => $perm) {

                    $permissionValue = $mapPermission->get($perm);

                    Permission::firstOrCreate([
                        'name' => $module.' '.$permissionValue,
                        'module' => $module,
                        'guard_name' => 'web',
                    ])->assignRole($role);

                    $this->command->info('Creating Permission to '.$permissionValue.' for '.$module);
                }

            }

        }

        $user = User::whereEmail('abdul@itrust.co.tz')->first();
        $user->assignRole('Super');

        $user = User::whereEmail('zubeda@itrust.co.tz')->first();
        $user->assignRole('Trade Inputer');

        $user = User::whereEmail('frank@itrust.co.tz')->first();
        $user->assignRole('Trade Authorizer');

        $user = User::whereEmail('francis@itrust.co.tz')->first();
        $user->assignRole('Finance Inputer');

        $user = User::whereEmail('salim@imaan.co.tz')->first();
        $user->assignRole('Finance Authorizer');

        $user = User::whereEmail('faiz@itrust.co.tz')->first();
        $user->assignRole('Chief Officer');

        return true;
    }

    /**
     * Truncates all the tables and the users table
     */
    public function truncateLaratrustTables(): void
    {
        $this->command->info('Truncating User, Role and Permission tables');
        Schema::disableForeignKeyConstraints();

        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('modules')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
