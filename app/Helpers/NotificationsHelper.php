<?php

// Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Data\PusherEventData;
use App\Events\PermissionsNotification;

class NotificationsHelper
{
    public static function permissionsChanged($target = 0): void
    {
        $event = new PusherEventData();
        $event->targets = $target;
        $event->channel = 'permissions';
        $event->event = 'permissions';
        $event->title = '';
        $event->message = '';
        event(new PermissionsNotification($event));
    }
}
