<?php

namespace App\Jobs\Statements;

use App\Mail\CustomEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Transactions\Http\Controllers\TransactionsController;

class UpdateWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
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
        } catch (\Exception $exception) {
            report($exception);
            $mailable = new CustomEmail('LOCKMINDS - Statement Update Failed ', " failed to complete statement update \n Exception message \n ".$exception->getMessage());
            Mail::to('canwork.job@gmail.com')->queue($mailable);
        }
    }
}
