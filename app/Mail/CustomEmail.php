<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;

    /**
     * The tittle instance.
     */
    public string $message;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, string $message)
    {
        $this->message = $message;
        $this->title = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject($this->title)->tag('statement')->markdown('emails.custom');
    }
}
