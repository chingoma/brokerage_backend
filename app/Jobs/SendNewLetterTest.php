<?php

namespace App\Jobs;

use App\Mail\MarketReports\CustomReportMailable;
use App\Models\MailingList;
use App\Models\MarketReports\MarketCustomReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewLetterTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected MarketCustomReport $report;

    /**
     * Create a new job instance.
     */
    public function __construct(MarketCustomReport $report)
    {
        $this->report = $report;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            DB::beginTransaction();
            $report = MarketCustomReport::first($this->report->id);
            $mailable = new CustomReportMailable(data: $report);
            if (! empty($report->file_name)) {
                if (! empty($report->file_name)) {
                    $mailable->setAttachment(public_path('storage/documents/'.$report->file_name));
                }
            }

            $primary = MailingList::where('category', 'newsletter primary')->first();
            if (! empty($primary)) {
                Mail::to($primary)
                    ->queue($mailable);
            }

            DB::commit();

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);
        }
    }
}
