<?php

namespace App\Jobs;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCourseToOpenAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $courseId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $courseId)
    {
        $this->courseId = $courseId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $course = Course::with([
            'lessons',
            'quizzes',
            'assignments'
        ])->find($this->courseId);

        if (!$course) {
            Log::warning("OpenAI Sync: Course not found {$this->courseId}");
            return;
        }

        /*
        ------------------------------------------------
        YOUR OPENAI VECTOR INDEX LOGIC GOES HERE
        ------------------------------------------------
        */

        Log::info("OpenAI Sync completed for course {$this->courseId}");
    }
}