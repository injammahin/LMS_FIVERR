<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class StudentSubmittedWork extends Notification
{
    use Queueable;

    public function __construct(
        public string $type, // assignment|quiz
        public int $courseId,
        public string $courseTitle,
        public string $studentName,
        public string $title,
        public string $url
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'course_id' => $this->courseId,
            'course_title' => $this->courseTitle,
            'student' => $this->studentName,
            'title' => $this->title,
            'url' => $this->url,
        ];
    }
}