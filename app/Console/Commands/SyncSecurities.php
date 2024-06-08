<?php

namespace App\Console\Commands;

use App\Mail\CustomEmail;
use App\Models\Security;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\DSE\Helpers\DSEHelper;
use Throwable;

class SyncSecurities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lockminds:sync-securities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Securities';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $securities = DSEHelper::marketData();
            if (! empty($securities)) {
                foreach ($securities as $security) {
                    $local = Security::where('name', $security->securityName)->first();
                    if (! empty($local)) {
                        $local->dse_reference = $security->securityRef;
                        $local->save();
                    }
                }
            }

            return self::SUCCESS;
        } catch (\Exception $exception) {
            report($exception);
            $mailable = new CustomEmail('LOCKMINDS - Statement Update Failed ', " failed to complete statement update \n Exception message \n ".$exception->getMessage());
            Mail::to('canwork.job@gmail.com')->queue($mailable);

            return self::FAILURE;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $mailable = new CustomEmail('LOCKMINDS - Statement Update Failed ', $exception->getMessage());
        Mail::to('canwork.job@gmail.com')->queue($mailable);
    }
}
