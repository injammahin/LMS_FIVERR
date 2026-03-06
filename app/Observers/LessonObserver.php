<?php

namespace App\Observers;

use App\Models\Lesson;
use App\Jobs\SyncCourseToOpenAI;

class LessonObserver
{
    public function saved(Lesson $lesson): void
    {
        if ($lesson->course_id) {
            SyncCourseToOpenAI::dispatch($lesson->course_id);
        }
    }
}