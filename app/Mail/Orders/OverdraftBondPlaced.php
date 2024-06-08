<?php

namespace App\Mail\Orders;

use App\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OverdraftBondPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public string $attachment = '';

    public mixed $options = [];

    /**
     * The tittle instance.
     */
    public string $title;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, array $options = [])
    {
        $business = Helper::business();
        $this->title = $title;
        $this->options = $options;
        $this->options['support_email'] = $business->email;
        $this->options['logo'] = $business->logo;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->markdown('emails.orders.overdraft-order-bond');
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
