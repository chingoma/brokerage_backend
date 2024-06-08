<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Spatie\SecurityAdvisoriesHealthCheck\SecurityAdvisoriesCheck;

class ApplicationHealthProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //        $dseSettings = \DB::table("dse_settings")->first();
        Health::checks([
            UsedDiskSpaceCheck::new(),
            DatabaseCheck::new(),
            SecurityAdvisoriesCheck::new(),
            ScheduleCheck::new(),
            CpuLoadCheck::new()
                ->failWhenLoadIsHigherInTheLast5Minutes(2.0)
                ->failWhenLoadIsHigherInTheLast15Minutes(1.5),
            //            PingCheck::new()->everyMinute()->headers([
            //                'Content-Type' => 'application/json',
            //                'Accept' => 'application/json',
            //                'Authorization' => 'Bearer '.$dseSettings->access_token,
            //            ])->failureMessage("DSE link is Down")
            //                ->url($dseSettings->base_url.'/stakeholders/external/brokers')->name('DSE')
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
