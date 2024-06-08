<?php

namespace App\Jobs;

use App\Mail\MarketReports\WeeklyReportsMailable;
use App\Models\MarketReports\Weekly\WeeklyMarketReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnqueueWeeklyReportEmailSending implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private WeeklyMarketReport $report;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WeeklyMarketReport $report)
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

            $mailable = new WeeklyReportsMailable(report: $this->report);
            $mailable->subject('Market Weekly');

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
