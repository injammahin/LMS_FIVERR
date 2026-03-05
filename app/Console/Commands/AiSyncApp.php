<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AiSyncApp extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai:sync-app {--course=}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-index LMS content to AI knowledge base (vector store)';

    public function handle(): int
    {
        $courseId = $this->option('course');

        if ($courseId) {
            $this->info("Sync requested for course_id={$courseId}");
            // Later you can dispatch job here:
            // \App\Jobs\SyncCourseToOpenAI::dispatch((int)$courseId);
            return self::SUCCESS;
        }

        $this->info("Sync requested for all courses");
        // Later you can dispatch all courses:
        // \App\Models\Course::select('id')->chunk(50, function($rows){
        //     foreach($rows as $r) \App\Jobs\SyncCourseToOpenAI::dispatch($r->id);
        // });

        return self::SUCCESS;
    }
}