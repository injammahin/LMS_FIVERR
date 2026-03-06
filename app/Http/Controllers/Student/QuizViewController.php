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
        // Ensure quiz belongs to course
        abort_if($quiz->course_id !== $course->id, 404);

        // ❗ Only allow published quizzes
        abort_if($quiz->status !== 'published', 404);

        $user = auth()->user();

        // Division check (optional)
        $divisionId = optional(optional($course->subject)->division)->id;
        // abort_if((int)$user->division_id !== (int)$divisionId, 403);

        // Load question count
        $quiz->loadCount('questions');

        // Count submitted attempts
        $usedAttempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->count();

        // Check if in-progress attempt exists
        $inProgress = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->exists();

        return view('student.quiz', compact(
            'course',
            'quiz',
            'usedAttempts',
            'inProgress'
        ));
    }
}