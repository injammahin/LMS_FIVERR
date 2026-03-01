<?php

namespace App\Notifications;

use App\Models\QuizAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuizAttemptGraded extends Notification
{
    use Queueable;

    public function __construct(public QuizAttempt $attempt)
    {
    }

    public function via($notifiable): array
    {
        // ✅ Database notification (instant, no queue worker needed)
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $quizTitle = $this->attempt->quiz?->title ?? 'Quiz';
        $courseTitle = $this->attempt->quiz?->course?->title ?? null;

        $score = (int)($this->attempt->score ?? 0);
        $total = (int)($this->attempt->total ?? 0);

        // Put a safe URL (don’t use unknown route names)
        $url = url('/student/attempts/' . $this->attempt->id . '/result');

        return [
            'type' => 'quiz_graded',
            'title' => 'Quiz graded',
            'message' => "{$quizTitle} has been graded.",
            'quiz_title' => $quizTitle,
            'course_title' => $courseTitle,
            'attempt_id' => $this->attempt->id,
            'score' => $score,
            'total' => $total,
            'url' => $url,
        ];
    }
}