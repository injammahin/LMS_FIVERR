<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewChatMessage extends Notification
{

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [

            'message_id'=>$this->message->id,

            'sender'=>$this->message->sender->name,

            'text'=>$this->message->message,

            'url'=>url('/chat/'.$this->message->sender_id)

        ];
    }
}