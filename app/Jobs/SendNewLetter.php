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

class SendNewLetter implements ShouldQueue
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
            $report = MarketCustomReport::find($this->report->id);
            $mailable = new CustomReportMailable(data: $report);
            $mailable->subject($report->title);
            $report->status = 'Sent';
            $report->save();
            if (! empty($report->file_name)) {
                if (! empty($report->file_name)) {
                    $mailable->setAttachment(public_path('storage/documents/'.$report->file_name));
                }
            }

            $recipients = match (strtolower($report->category_id)) {
                'all (users & customers)' => DB::table('users')->pluck('email'),
                'all customers only' => DB::table('users')->whereIn('type', ['individual', 'corporate', 'joint'])->pluck('email'),
                'all users only' => DB::table('users')->whereNull('type')->pluck('email'),
                default => DB::table('users')->where('category_id', $report->category_id)->pluck('email'),
            };

            $emails = [];
            foreach ($recipients as $key => $recipient) {
                $emails[$key] = $recipient;
            }

            if (! empty($emails)) {
                $primary = MailingList::where('category', 'newsletter primary')->first();
                if (! empty($primary)) {
                    Mail::to($primary)
                        ->bcc($emails)
                        ->queue($mailable);
                }
            }

            DB::commit();

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);
        }
    }
}
