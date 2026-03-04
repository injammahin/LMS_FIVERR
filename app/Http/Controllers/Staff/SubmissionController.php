<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\QuizAttempt;
use App\Models\Assignment;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $staff = auth()->user();

        // filters
        $filter = $request->get('type', 'all');      // all|assignment|quiz
        $status = $request->get('status', 'all');    // all|pending|graded
        $search = trim((string) $request->get('search', ''));
        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 15;

        // staff courses
        $courseIds = $staff->coursesSupporting()->pluck('courses.id')->toArray();

        // -------------------------
        // ASSIGNMENT SUBMISSIONS
        // -------------------------
        $assignmentSubs = null;
        $assignmentBase = AssignmentSubmission::query()
            ->with(['user', 'assignment.course.subject.division'])
            ->whereHas('assignment.course', fn($q) => $q->whereIn('courses.id', $courseIds));

        // show ONLY submitted+graded (optional: if you want all)
        $assignmentBase->whereIn('status', ['submitted', 'graded']);

        if ($status === 'graded') {
            $assignmentBase->where('status', 'graded');
        } elseif ($status === 'pending') {
            $assignmentBase->where('status', 'submitted');
        }

        if ($search) {
            $assignmentBase->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                })->orWhereHas('assignment', function ($a) use ($search) {
                    $a->where('title', 'like', "%{$search}%");
                })->orWhereHas('assignment.course', function ($c) use ($search) {
                    $c->where('title', 'like', "%{$search}%");
                });
            });
        }

        if ($filter === 'all' || $filter === 'assignment') {
            $assignmentSubs = (clone $assignmentBase)
                ->orderByDesc('updated_at')
                ->paginate($perPage, ['*'], 'apage')
                ->appends($request->except('apage'));
        }

        // -------------------------
        // QUIZ ATTEMPTS
        // -------------------------
        $quizAttempts = null;
        $quizBase = QuizAttempt::query()
            ->with(['user', 'quiz.course.subject.division'])
            ->whereNotNull('submitted_at')
            ->whereHas('quiz.course', fn($q) => $q->whereIn('courses.id', $courseIds))

            // ✅ avoids N+1 attempt count
            ->select('quiz_attempts.*')
            ->selectSub(function ($q) {
                $q->from('quiz_attempts as qa2')
                  ->selectRaw('COUNT(*)')
                  ->whereColumn('qa2.quiz_id', 'quiz_attempts.quiz_id')
                  ->whereColumn('qa2.user_id', 'quiz_attempts.user_id')
                  ->whereNotNull('qa2.submitted_at');
            }, 'attempt_used');

        if ($status === 'graded') {
            $quizBase->where('status', 'graded');
        } elseif ($status === 'pending') {
            $quizBase->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'graded');
            });
        }

        if ($search) {
            $quizBase->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                })->orWhereHas('quiz', function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%");
                })->orWhereHas('quiz.course', function ($c) use ($search) {
                    $c->where('title', 'like', "%{$search}%");
                });
            });
        }

        if ($filter === 'all' || $filter === 'quiz') {
            $quizAttempts = (clone $quizBase)
                ->orderByDesc('submitted_at')
                ->paginate($perPage, ['*'], 'qpage')
                ->appends($request->except('qpage'));
        }

        // -------------------------
        // COUNTS (for header chips)
        // -------------------------
        $countAssignments = (clone $assignmentBase)->count();
        $countQuizzes = (clone $quizBase)->count();

        $countAssignmentsPending = (clone $assignmentBase)->where('status', 'submitted')->count();
        $countAssignmentsGraded  = (clone $assignmentBase)->where('status', 'graded')->count();

        $countQuizzesGraded = (clone $quizBase)->where('status', 'graded')->count();
        $countQuizzesPending = (clone $quizBase)->where(function ($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'graded');
        })->count();

        return view('staff.submissions.index', compact(
            'assignmentSubs',
            'quizAttempts',
            'filter',
            'status',
            'search',
            'perPage',
            'countAssignments',
            'countQuizzes',
            'countAssignmentsPending',
            'countAssignmentsGraded',
            'countQuizzesPending',
            'countQuizzesGraded'
        ));
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
        public function showAssignment(Assignment $assignment, AssignmentSubmission $submission)
    {
        $staff = auth()->user();
                /** @var \App\Models\User $staff */
        abort_if(!$staff->coursesSupporting()->where('courses.id', $assignment->course_id)->exists(), 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $submission->load(['user','assignment.course']);

        return view('staff.submissions.assignment_show', compact('assignment','submission'));
    }
}