<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = [
            'id' => $notification->id,
            'user_id' => (string) $notification->user_id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'icon' => $notification->icon,
            'url' => $notification->url,
            'is_read' => (bool) $notification->is_read,
            'created_at' => $notification->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-notifications.' . $this->notification['user_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
