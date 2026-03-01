<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;

class LessonViewController extends Controller
{
    public function show(Course $course, Lesson $lesson)
    {
        abort_if($lesson->course_id !== $course->id, 404);

        $user = auth()->user();

        // ✅ Division guard
        $divisionId = optional(optional($course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        // ✅ Mark as VIEWED (only set viewed_at if it's still null)
        LessonProgress::updateOrCreate(
            ['lesson_id' => $lesson->id, 'user_id' => $user->id],
            ['viewed_at' => now()]
        );

        $progress = LessonProgress::where('lesson_id', $lesson->id)
            ->where('user_id', $user->id)
            ->first();

        return view('student.lesson', compact('course', 'lesson', 'progress'));
    }

    public function markDone(Course $course, Lesson $lesson)
    {
        abort_if($lesson->course_id !== $course->id, 404);

        $user = auth()->user();

        // ✅ Division guard
        $divisionId = optional(optional($course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        // ✅ Mark as DONE (completed_at fixed once)
        $progress = LessonProgress::firstOrCreate(
            ['lesson_id' => $lesson->id, 'user_id' => $user->id],
            ['viewed_at' => now()]
        );

        if (!$progress->completed_at) {
            $progress->completed_at = now();
            if (!$progress->viewed_at) $progress->viewed_at = now();
            $progress->save();
        }

        return back()->with('success', 'Lesson marked as done.');
    }
}