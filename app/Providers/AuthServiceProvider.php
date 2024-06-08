<?php

namespace App\Providers;

use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //        Gate::define('viewPulse', function (User $user) {
        //            return true;// in_array($user->email,Helper::admins());
        //        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            if ($user->is_admin) {
                return getenv('APP_URL').'/reset-password/'.$token.'?email='.$user->email;
            } else {
                return 'https://192.168.1.41:40412/#/reset-password?email='.$user->email.'&token='.$token;
            }
        });

    }
}
