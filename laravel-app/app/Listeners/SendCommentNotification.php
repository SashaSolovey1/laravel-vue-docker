<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommentNotification;

class SendCommentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  \App\Events\CommentCreated  $event
     * @return void
     */
    public function handle(CommentCreated $event)
    {
        // Отправка уведомления по электронной почте
        Mail::to('solo160103@gmail.com')->send(new CommentNotification($event->comment));
    }
}
