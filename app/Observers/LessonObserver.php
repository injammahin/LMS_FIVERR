<?php

namespace App\Observers;

use App\Jobs\SyncCourseToOpenAI;
use App\Models\Lesson;

class LessonObserver
{
    public function saved(Lesson $lesson): void
    {
        SyncCourseToOpenAI::dispatch($lesson->course_id);
    }
}