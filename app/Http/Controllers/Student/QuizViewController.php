<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;

class QuizViewController extends Controller
{
    public function show(Course $course, Quiz $quiz)
    {
        abort_if($quiz->course_id !== $course->id, 404);

        $user = auth()->user();
        $divisionId = optional(optional($course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        $quiz->loadCount('questions');

        return view('student.quiz', compact('course', 'quiz'));
    }
}