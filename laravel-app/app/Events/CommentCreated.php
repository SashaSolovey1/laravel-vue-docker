<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $comment;

    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    public function broadcastWith()
    {
        return [
            'comment' => $this->comment,
        ];
    }

    public function broadcastAs()
    {
        return 'newComment';
    }

    public function broadcastOn()
    {
        return new Channel('comments');
    }
}
