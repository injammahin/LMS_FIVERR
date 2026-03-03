<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $staff = auth()->user();
                /** @var \App\Models\User $staff */

        $courseIds = $staff->coursesSupporting()->pluck('courses.id')->toArray();

        $filter = $request->get('type','all'); // all|assignment|quiz

        $assignmentSubs = null;
        $quizAttempts = null;

        if ($filter === 'all' || $filter === 'assignment') {
            $assignmentSubs = AssignmentSubmission::query()
                ->with(['user','assignment.course'])
                ->whereHas('assignment.course', fn($q) => $q->whereIn('id', $courseIds))
                ->latest()
                ->paginate(15, ['*'], 'assignments_page')
                ->appends($request->query());
        }

        if ($filter === 'all' || $filter === 'quiz') {
            $quizAttempts = QuizAttempt::query()
                ->with(['user','quiz.course'])
                ->whereHas('quiz.course', fn($q) => $q->whereIn('id', $courseIds))
                ->whereIn('status', ['submitted','reviewed','graded'])
                ->latest()
                ->paginate(15, ['*'], 'quizzes_page')
                ->appends($request->query());
        }

        return view('staff.submissions.index', compact('assignmentSubs','quizAttempts','filter'));
    }

    public function showAssignment(Assignment $assignment, AssignmentSubmission $submission)
    {
        $staff = auth()->user();
                /** @var \App\Models\User $staff */
        abort_if(!$staff->coursesSupporting()->where('courses.id', $assignment->course_id)->exists(), 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $submission->load(['user','assignment.course']);

        return view('staff.submissions.assignment_show', compact('assignment','submission'));
    }

    public function showAttempt(QuizAttempt $attempt)
    {
        $staff = auth()->user();

        $attempt->load(['user','quiz.course','answers.question.options']);
        /** @var \App\Models\User $staff */
        abort_if(!$staff->coursesSupporting()->where('courses.id', $attempt->quiz->course_id)->exists(), 403);

        // ✅ View only — DO NOT grade, DO NOT change status here
        return view('staff.submissions.quiz_attempt_show', compact('attempt'));
    }
}