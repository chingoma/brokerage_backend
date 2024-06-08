<?php

namespace App\Mail\Orders;

use App\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Bonds\Entities\BondOrder;
use Modules\Orders\Entities\Order;

class BondExecuted extends Mailable
{
    use Queueable, SerializesModels;

    public string $attachment = '';

    public mixed $options = [];

    /**
     * The order instance.
     *
     * @var Order
     */
    public BondOrder $order;

    /**
     * Create a new message instance.
     */
    public function __construct(BondOrder $order, array $options = [])
    {
        $business = Helper::business();
        $this->order = $order;
        $this->options = $options;
        $this->options['support_email'] = $business->email;
        $this->options['logo'] = $business->logo;
        $this->options['facebook'] = $business->facebook;
        $this->options['twitter'] = $business->twitter;
        $this->options['instagram'] = $business->instagram;
        if (strtolower($order->type) == 'buy') {
            $this->options['type'] = 'BUY';
        } else {
            $this->options['type'] = 'SELL';
        }

        if ($order->balance > 0) {
            $this->options['status'] = 'partially';
        } else {
            $this->options['status'] = 'successfully';
        }

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->markdown('emails.bond.buy-executed')->attach($this->attachment, [
            'as' => 'contract-note.pdf',
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
