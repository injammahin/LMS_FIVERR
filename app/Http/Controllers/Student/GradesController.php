<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class GradesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $filter = $request->get('type', 'all'); // all|quiz|assignment

        $quizAttempts = null;
        $assignmentSubs = null;

        if ($filter === 'all' || $filter === 'quiz') {
            $quizAttempts = QuizAttempt::query()
                ->with(['quiz.course'])
                ->where('user_id', $user->id)
                ->whereNotNull('submitted_at')
                ->latest('submitted_at')
                ->paginate(12, ['*'], 'quizzes_page')
                ->appends($request->query());
        }

        if ($filter === 'all' || $filter === 'assignment') {
            $assignmentSubs = AssignmentSubmission::query()
                ->with(['assignment.course'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(12, ['*'], 'assignments_page')
                ->appends($request->query());
        }

        // summary
        $gradedQuizCount = QuizAttempt::where('user_id', $user->id)->where('status', 'graded')->count();
        $gradedAssignmentCount = AssignmentSubmission::where('user_id', $user->id)->where('status', 'graded')->count();

        $avgQuizPercent = (int) round(
            QuizAttempt::where('user_id', $user->id)
                ->whereNotNull('submitted_at')
                ->selectRaw('AVG(CASE WHEN total > 0 THEN (score * 100.0 / total) END) as avgp')
                ->value('avgp') ?? 0
        );

        return view('student.grades.index', compact(
            'filter',
            'quizAttempts',
            'assignmentSubs',
            'gradedQuizCount',
            'gradedAssignmentCount',
            'avgQuizPercent'
        ));
    }
}