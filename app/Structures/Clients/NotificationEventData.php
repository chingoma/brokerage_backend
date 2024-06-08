<?php

namespace App\Structures\Clients;

use App\Models\User;

class NotificationEventData
{
    public string $title = 'Notification';

    public string $type = 'notification';

    public string $message = 'Notification';

    public string $channel = 'client-notifications';

    public string $event = 'notification';

    public string $target;

    public string $date;

    public User $causer;

    public function __construct(?User $user = null)
    {
        if (empty($user)) {
            $this->causer = User::find(auth()->user()->id);
        } else {
            $this->causer = $user;
        }

        $this->date = now()->toDateTimeString();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): NotificationEventData
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): NotificationEventData
    {
        $this->type = $type;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): NotificationEventData
    {
        $this->message = $message;

        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): NotificationEventData
    {
        $this->channel = $channel;

        return $this;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): NotificationEventData
    {
        $this->event = $event;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): NotificationEventData
    {
        $this->target = $target;

        return $this;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): NotificationEventData
    {
        $this->date = $date;

        return $this;
    }

    public function getCauser(): User
    {
        return $this->causer;
    }

    public function setCauser(User $causer): NotificationEventData
    {
        $this->causer = $causer;

        return $this;
    }
}
