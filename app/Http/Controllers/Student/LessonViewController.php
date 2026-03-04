<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Division;

class LessonViewController extends Controller
{
public function show(Course $course, Lesson $lesson)
{
    abort_if($lesson->course_id !== $course->id, 404);

    // ✅ block drafts too (students)
    abort_if($course->status !== 'published', 404);

    $user = auth()->user();

    $userDivision = Division::find($user->division_id);
    abort_if(!$userDivision, 403);

    $courseDivision = $course->subject?->division;   // make sure relationship exists
    abort_if(!$courseDivision, 404);

    // ✅ allow if course division level <= user level
    abort_if($courseDivision->level > $userDivision->level, 403);

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
    // ✅ Ensure lesson belongs to course
    abort_if((int) $lesson->course_id !== (int) $course->id, 404);

    // ✅ Students can't access draft courses
    abort_if($course->status !== 'published', 404);

    $user = auth()->user();

    // ✅ Load needed relations safely
    $course->loadMissing('subject.division');

    $userDivision = Division::find($user->division_id);
    abort_if(!$userDivision, 403);

    $courseDivision = $course->subject?->division;
    abort_if(!$courseDivision, 404);

    // ✅ Same rule as SubjectBrowseController:
    // allow access if course division level <= user level
    abort_if((int) $courseDivision->level > (int) $userDivision->level, 403);

    // ✅ Mark DONE (create progress if missing)
    $progress = LessonProgress::firstOrCreate(
        ['lesson_id' => $lesson->id, 'user_id' => $user->id],
        ['viewed_at' => now()]
    );

    if (empty($progress->viewed_at)) {
        $progress->viewed_at = now();
    }

    if (empty($progress->completed_at)) {
        $progress->completed_at = now();
    }

    $progress->save();

    return back()->with('success', 'Lesson marked as done.');
}
}