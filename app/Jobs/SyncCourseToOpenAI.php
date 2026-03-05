<?php

namespace App\Console\Commands;

use App\Jobs\SyncCourseToOpenAI;
use App\Models\Course;
use Illuminate\Console\Command;

class AiSyncApp extends Command
{
    protected $signature = 'ai:sync-app {--course=}';
    protected $description = 'Auto-index LMS content (courses/lessons/quizzes/assignments) to OpenAI vector stores';

    public function handle(): int
    {
        $courseId = $this->option('course');

        if ($courseId) {
            SyncCourseToOpenAI::dispatch((int)$courseId);
            $this->info("Queued auto sync for course #{$courseId}");
            return self::SUCCESS;
        }

        $courses = Course::select('id')->get();
        foreach ($courses as $c) {
            SyncCourseToOpenAI::dispatch((int)$c->id);
        }

        $this->info("Queued auto sync for " . $courses->count() . " courses.");
        return self::SUCCESS;
    }
}