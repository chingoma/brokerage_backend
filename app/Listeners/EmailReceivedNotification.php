<?php

namespace App\Listeners;

use App\Events\EmailReceived;

class EmailReceivedNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(EmailReceived $event)
    {
        //
    }
}
