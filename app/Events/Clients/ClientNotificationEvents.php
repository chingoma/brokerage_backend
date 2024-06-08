<?php

namespace App\Events\Clients;

use App\Data\Clients\NotificationEventData;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientNotificationEvents implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public NotificationEventData $eventData;

    /**
     * Create a new event instance.
     */
    public function __construct(NotificationEventData $eventData)
    {
        $this->eventData = $eventData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array|Channel
    {
        return ['client-notifications'];
    }

    public function broadcastAs(): string
    {
        return 'client-notifications';
    }
}
