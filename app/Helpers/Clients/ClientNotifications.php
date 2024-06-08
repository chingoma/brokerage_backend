<?php

namespace App\Helpers\Clients;

use App\Data\Clients\NotificationEventData;
use App\Events\Clients\ClientNotificationEvents;
use App\Models\User;

class ClientNotifications
{
    public static function registrationEvent(User $user)
    {
        $event = new NotificationEventData($user);
        $event->message = 'New customer '.$user->profile->name.' registered';
        $event->title = 'A new Customer registered';
        event(new ClientNotificationEvents($event));
    }
}
