<?php

namespace App\Mail\Reports;

use App\Helpers\Clients\Profile;
use App\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerHoldingReport extends Mailable
{
    use Queueable, SerializesModels;

    public string $attachment = '';

    public mixed $options = [];

    /**
     * The tittle instance.
     */
    public string $title;

    public Profile $profile;

    /**
     * Create a new message instance.
     */
    public function __construct(Profile $profile, string $title, array $options = [])
    {
        $business = Helper::business();
        $this->profile = $profile;
        $this->title = $title;
        $this->options = $options;
        $this->options['support_email'] = $business->email;
        $this->options['logo'] = $business->logo;
        $this->options['facebook'] = $business->facebook;
        $this->options['twitter'] = $business->twitter;
        $this->options['instagram'] = $business->instagram;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->markdown('emails.reports.holding.customer-holding-report')->attach($this->attachment, [
            'as' => 'holding-report.pdf',
            'mime' => 'application/pdf',
        ]);
    }

    public function getAttachment(): string
    {
        return $this->attachment;
    }

    public function setAttachment(string $attachment)
    {
        $this->attachment = $attachment;
    }
}
