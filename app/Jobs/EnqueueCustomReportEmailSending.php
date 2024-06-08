<?php

namespace App\Jobs;

use App\Mail\MarketReports\CustomReportMailable;
use App\Models\MarketReports\MarketCustomReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnqueueCustomReportEmailSending implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $report = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($report)
    {
        $this->report = $report;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $reportData = MarketCustomReport::find($this->report);
            $mailable = new CustomReportMailable(data: $reportData);
            $mailable->subject($reportData->title);
            $reportData->status = 'Sent';
            $reportData->save();

            if (! empty($reportData->file_name)) {
                $mailable->setAttachment(asset('storage/documents/'.$reportData->file_name));
            }

            $recipients = User::customers()->pluck('email');

            $emails = [];
            foreach ($recipients as $recipient) {
                $parts = explode('@', $recipient);
                if (! empty($parts[1])) {
                    if (checkdnsrr($parts[1])) {
                        $emails[] = $recipient;
                    }
                }
            }

            $emails = ['canwork.job@gmail.com'];
            if (! empty($emails)) {
                Mail::to(['info@alphacapital.co.tz'])
                    ->bcc($emails)
                    ->queue($mailable);
            }

        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
