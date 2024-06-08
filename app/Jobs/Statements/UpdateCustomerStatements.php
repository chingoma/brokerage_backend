<?php

namespace App\Jobs\Statements;

use App\Models\Accounting\Transaction;
use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Transactions\Http\Controllers\TransactionsController;

class UpdateCustomerStatements implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $id;

    /**
     * Create a new job instance.
     */
    public function __construct(mixed $id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = DB::table('users')
                ->select(['users.name', 'users.id', 'profiles.address', 'users.dse_account', 'users.category_id'])
                ->whereIn('type', ['minor', 'individual', 'corporate', 'joint'])
                ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
                ->whereNull('users.deleted_at')
                ->where('users.id', $this->id)
                ->first();

            if (! empty($user)) {
                Statement::where('client_id', $user->id)->delete();

                $transactions = Transaction::where('status', 'approved')
                    ->whereNull('deleted_at')
                    ->groupBy('reference')
                    ->orderBy('transaction_date', 'asc')
                    ->where('client_id', $user->id)
                    ->get();

                TransactionsController::updateCustomerStatement(transactions: $transactions, user: $user);
            }

        } catch (\Exception $exception) {
            report($exception);
        }
    }
}
