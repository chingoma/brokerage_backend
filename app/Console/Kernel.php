<?php

namespace App\Console;

use App\Jobs\DSE\DSECrawler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('backup:run')->daily()->at('01:30');
//        $schedule->command('emailSync:1minutes')->everyFiveSeconds()->withoutOverlapping()->runInBackground();
//          $schedule->command('cache:prune-stale-tags')->hourly();
//          $schedule->command('telescope:prune --hours=24')->daily();
//        $schedule->job(new UpdateCustomerStatements())->everyMinute();
//        $schedule->job(new UpdateStatements)->everyFiveMinutes();
//        $schedule->job(new SyncReceipts())->everyFiveMinutes();
//          $schedule->job(new DSETokenRefresh())->everyFifteenSeconds();
//          $schedule->job(new AccountSyncAllJob())->everyFifteenMinutes();
//          $schedule->job(new VerifyDSEAccountsLinkage())->everyFifteenMinutes();
//          $schedule->job(new VerifyDSEAccounts())->everyFifteenMinutes();
//        $schedule->job(job: new UpdateWallet(),queue: "brokerlink-admin-api-wallets")->everyMinute();
//        $schedule->job(new SendPeriodicStatements)->everyMinute();
//        $schedule->job(new DeliveryReport())->everyFiveSeconds();

//        $schedule->command(\Spatie\Health\Commands\RunHealthChecksCommand::class)->everyMinute();
//        $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();

        $schedule->job(new DSECrawler())->dailyAt("17:00");
//        $schedule->job(new DSECrawler())->dailyAt("17:04");

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
