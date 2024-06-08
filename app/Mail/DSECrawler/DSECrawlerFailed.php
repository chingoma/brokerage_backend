<?php

namespace App\Mail\DSECrawler;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DSECrawlerFailed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->view("marketdata::emails.dse-crawler-failed");
    }
}
