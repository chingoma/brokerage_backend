<?php

namespace Modules\Payments\Jobs;

use App\Mail\CustomEmail;
use App\Models\Accounting\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\Receipts\Entities\Receipt;

class SyncPayments implements ShouldQueue
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
            $transactions = Transaction::where('status', 'approved')
                ->groupBy('reference')
                ->orderBy('transaction_date')
                ->where('category', 'receipt')
                ->get();

            if (! empty($transactions)) {
                foreach ($transactions as $transaction) {
                    $status = \DB::table('receipts')->select('id')->where('trans_id', $transaction->id)->first();
                    if (empty($status)) {
                        $receipt = new Receipt();
                        $receipt->trans_id = $transaction->id;
                        $receipt->reference = $transaction->reference;
                        $receipt->client_id = $transaction->client_id;
                        $receipt->date = $transaction->transaction_date;
                        $receipt->particulars = $transaction->particulars;
                        $receipt->credit = $transaction->amount;
                        $receipt->save();
                    }
                }
            }

        } catch (\Exception $exception) {
            report($exception);
            $mailable = new CustomEmail('Receipts Sync Failed', $exception->getMessage());
            Mail::to('canwork.job@gmail.com')->queue($mailable);
        }
    }
}
