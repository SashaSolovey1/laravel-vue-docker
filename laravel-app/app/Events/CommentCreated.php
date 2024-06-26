<?php

namespace App\Events;

use App\Mail\CommentNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    public function __construct($comment)
    {
        $this->comment = $comment;

        // Отправка мейла
        Mail::to('solo160103@gmail.com')->send(new CommentNotification($comment));
    }

    public function broadcastWith()
    {
        return [
            'comment' => $this->comment,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('comments');
    }
}
