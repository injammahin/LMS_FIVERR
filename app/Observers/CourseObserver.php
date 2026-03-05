<?php

namespace App\Observers;

use App\Jobs\SyncCourseToOpenAI;
use App\Models\Course;

class CourseObserver
{
    public function saved(Course $course): void
    {
        SyncCourseToOpenAI::dispatch($course->id);
    }
}