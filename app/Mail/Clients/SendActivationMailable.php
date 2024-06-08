<?php

namespace App\Mail\Clients;

use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendActivationMailable extends Mailable
{
    use Queueable, SerializesModels;

    public mixed $options = [];

    /**
     * The order instance.
     */
    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, array $options = [])
    {
        $business = Helper::business();
        $this->user = $user;
        $this->options = $options;
        $this->options['support_email'] = $business->email;
        $this->options['business'] = $business;
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
        return $this->subject('Account Activation Instruction')->markdown('emails.auth.account-activation');
    }
}
