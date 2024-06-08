<?php

namespace App\Console\Commands;

use App\Mail\CustomEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Transactions\Http\Controllers\TransactionsController;
use Modules\Wallet\Entities\Wallet;
use Throwable;

class InitializeWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lockminds:wallet-initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize customers wallets';

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
            Wallet::truncate();
            $users = DB::table('users')
                ->select(['users.name', 'users.id', 'profiles.address', 'users.dse_account', 'users.category_id'])
                ->whereIn('type', ['minor', 'individual', 'corporate', 'joint'])
                ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
                ->whereNull('users.deleted_at')
                ->get();

            if (! empty($users)) {
                foreach ($users as $user) {
                    TransactionsController::updateWallet($user->id);
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
