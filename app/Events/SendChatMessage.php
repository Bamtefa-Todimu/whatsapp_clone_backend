<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private User $user;
    private string $message;
    private  $chat;

    /**
     * Create a new event instance.
     */
    public function __construct($user, $message, $chat_id)
    {
        //
        $this->user = $user;
        $this->message = $message;
        $this->chat = $chat_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {

        return [
            // new PrivateChannel('Send-Message.' . $this->user->id),
            new Channel('testing.' . $this->user->id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'chat' => $this->chat,
            // 'message' => $this->message,
            // 'user' => $this->user->only(['name', 'email'])
        ];
    }
}
