<?php

namespace App\Jobs\Wallet;

use App\Helpers\Pdfs\StatementPdf;
use App\Mail\CustomEmail;
use App\Models\Accounting\Transaction;
use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Wallet\Entities\Wallet;

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
                    if (! empty($user)) {
                        Statement::where('client_id', $user->id)->delete();

                        $transactions = Transaction::where('status', 'approved')
                            ->groupBy('reference')
                            ->orderBy('transaction_date', 'asc')
                            ->where('client_id', $user->id)
                            ->get();

                        if (! empty($transactions)) {
                            $pdf = new StatementPdf(false);
                            $pdf->create($transactions, $user);
                            $statements = $pdf->statement;
                            if (! empty($statements)) {
                                foreach ($statements as $transaction) {
                                    $transaction = (object) $transaction;
                                    $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
                                    $wallet->available_balance = $transaction->balance;
                                    $wallet->actual_balance = $transaction->balance;
                                    $wallet->state = $transaction->state;
                                    $wallet->save();
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            $mailable = new CustomEmail('LOCKMINDS - Statement Update Failed ', " failed to complete statement update \n Exception message \n ".$exception->getMessage());
            Mail::to('canwork.job@gmail.com')->queue($mailable);
        }
    }
}
