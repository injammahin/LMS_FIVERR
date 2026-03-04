<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
class QuizViewController extends Controller
{
    public function show(Course $course, Quiz $quiz)
    {
        abort_if($quiz->course_id !== $course->id, 404);

        $user = auth()->user();
        $divisionId = optional(optional($course->subject)->division)->id;
        // abort_if((int)$user->division_id !== (int)$divisionId, 403);

        $quiz->loadCount('questions');
        $usedAttempts = QuizAttempt::where('quiz_id', $quiz->id)
        ->where('user_id', auth()->id())
        ->whereNotNull('submitted_at')
        ->count();

       $inProgress = QuizAttempt::where('quiz_id', $quiz->id)
        ->where('user_id', auth()->id())
        ->where('status', 'in_progress')
        ->exists();

    return view('student.quiz', compact('course','quiz','usedAttempts','inProgress'));    }
}