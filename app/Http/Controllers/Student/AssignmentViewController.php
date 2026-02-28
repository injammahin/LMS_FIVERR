<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Assignment;

class AssignmentViewController extends Controller
{
    public function show(Course $course, Assignment $assignment)
    {
        abort_if($assignment->course_id !== $course->id, 404);

        $user = auth()->user();
        $divisionId = optional(optional($course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        return view('student.assignment', compact('course', 'assignment'));
    }
}