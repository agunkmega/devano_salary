<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastTestEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $title;
    public string $message;
    public string $type;
    public ?int $targetUserId;

    public function __construct(string $title, string $message, string $type = 'info', ?int $targetUserId = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->targetUserId = $targetUserId;
    }

    public function broadcastOn(): array
    {
        if ($this->targetUserId) {
            return [
                new Channel('private-notifications.' . $this->targetUserId),
                new PrivateChannel('notifications.' . $this->targetUserId),
                new PrivateChannel('private-notifications.' . $this->targetUserId),
            ];
        }

        return [
            new Channel('presence-chat'),
            new PresenceChannel('chat'),
            new PresenceChannel('presence-chat'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'broadcast.test';
    }

    public function broadcastWith(): array
    {
        return [
            'is_test' => true,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'target_user_id' => $this->targetUserId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
