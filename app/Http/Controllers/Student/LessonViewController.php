<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;

class LessonViewController extends Controller
{
    public function show(Course $course, Lesson $lesson)
    {
        abort_if($lesson->course_id !== $course->id, 404);

        $user = auth()->user();

        // ✅ must be assigned to the division that owns this course's subject
        $divisionId = optional(optional($course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        return view('student.lesson', compact('course', 'lesson'));
    }
}