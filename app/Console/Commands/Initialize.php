<?php

namespace App\Console\Commands;

use App\Mail\CustomEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\SMS\Helpers\SmsFunctions;
use Modules\Whatsapp\Helpers\WhatsappMessagesHelper;
use Throwable;

class Initialize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lockminds:initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize application';

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
            Schema::disableForeignKeyConstraints();
            Artisan::call('optimize:clear');
            Artisan::call('db:wipe');
            Artisan::call('migrate');
            Artisan::call('db:seed');
            Artisan::call('lockminds:wallet-initialize');
            Schema::enableForeignKeyConstraints();
            SmsFunctions::send_sms(recipients: ['255746251394'], message: 'This is sample text message number '.random_int(11111, 99999));
            WhatsappMessagesHelper::sendTextMessage(message: 'Setup completed successfully.', recipient: '255746251394');

            return self::SUCCESS;
        } catch (\Exception $exception) {
            report($exception);
            $mailable = new CustomEmail('LOCKMINDS - Application initialization failed ', $exception->getMessage());
            Mail::to('canwork.job@gmail.com')->queue($mailable);

            return self::FAILURE;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $mailable = new CustomEmail('LOCKMINDS - Application initialization failed ', $exception->getMessage());
        Mail::to('canwork.job@gmail.com')->queue($mailable);
    }
}
