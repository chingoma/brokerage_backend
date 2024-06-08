<?php

namespace App\Mail;

use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $options = [];

    /**
     * The order instance.
     *
     * @var User
     */
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $options = [])
    {
        $business = Helper::business();
        $this->user = $user;
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
    public function build()
    {
        return $this->markdown('emails.auth.reset-password');
    }
}
