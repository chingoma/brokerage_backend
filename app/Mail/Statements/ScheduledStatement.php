<?php

namespace App\Mail\Statements;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduledStatement extends Mailable
{
    use Queueable, SerializesModels;

    public string $tittle = 'Periodic Statement';

    public string $message = 'Customer Periodic Statement Report';

    public string $attachment = '';

    public mixed $options = [];

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject('Periodic Statement Report')->markdown('emails.Statements.scheduled-statements')->attach($this->attachment, [
            'as' => 'customer-period-statement-report.pdf',
            'mime' => 'application/pdf',
        ]);
    }

    public function setAttachment(string $attachment)
    {
        $this->attachment = $attachment;
    }
}
