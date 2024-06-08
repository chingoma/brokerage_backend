<?php

namespace App\Mail\Clients;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmailMailable extends Mailable
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
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to iTrust Finance',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.welcome-message',
        );
    }
}
