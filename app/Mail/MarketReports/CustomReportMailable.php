<?php

namespace App\Mail\MarketReports;

use App\Helpers\Helper;
use App\Models\Business;
use App\Models\MarketReports\MarketCustomReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomReportMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $attachment = '';

    public mixed $options = [];

    public Business $business;

    /**
     * The MarketCustomReport instance.
     */
    public MarketCustomReport $data;

    /**
     * Create a new message instance.
     */
    public function __construct(MarketCustomReport $data, array $options = [])
    {
        $this->business = Helper::business();
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        // return $this->markdown('emails.market-reports.custom-report');
        if (! empty($this->data->file_name)) {
            return $this->markdown('emails.market-reports.custom-report')->attach($this->attachment, [
                'as' => strtolower(str_replace(' ', '_', $this->data->title)).'.'.$this->data->file_ext,
            ]);
        } else {
            return $this->markdown('emails.market-reports.custom-report');
        }
    }

    public function getAttachment(): string
    {
        return $this->attachment;
    }

    public function setAttachment(string $attachment)
    {
        if (! empty($attachment)) {
            $this->attachment = $attachment;
        }
    }
}
