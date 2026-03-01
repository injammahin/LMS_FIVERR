<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\TeacherCounts;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

class DashboardController extends Controller
{
    use TeacherCounts;

    public function index()
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        $courses = $teacher->coursesTeaching()->with(['subject.division'])->orderBy('title')->get();
        $courseIds = $courses->pluck('id')->all();

        $divisionIds = $courses->pluck('subject.division_id')->filter()->unique()->values()->all();

        $studentsCount = User::where('role', 'student')
            ->when(!empty($divisionIds), fn($q) => $q->whereIn('division_id', $divisionIds))
            ->count();

        $lessonsTotal = Lesson::whereIn('course_id', $courseIds)->count();
        $quizzesTotal = Quiz::whereIn('course_id', $courseIds)->count();
        $assignmentsTotal = Assignment::whereIn('course_id', $courseIds)->count();

        $quizIds = Quiz::whereIn('course_id', $courseIds)->pluck('id')->all();
        $assignmentIds = Assignment::whereIn('course_id', $courseIds)->pluck('id')->all();

        $quizSubmittedCount = QuizAttempt::whereIn('quiz_id', $quizIds)->whereNotNull('submitted_at')->count();
        $assignmentSubmittedCount = AssignmentSubmission::whereIn('assignment_id', $assignmentIds)->count();

        $pendingGrading = $this->pendingCount($courseIds);

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        $quizPercent = $quizzesTotal > 0 ? round(($quizSubmittedCount / max(1, $quizzesTotal)) * 100) : 0;
        $assignPercent = $assignmentsTotal > 0 ? round(($assignmentSubmittedCount / max(1, $assignmentsTotal)) * 100) : 0;

        return view('teacher.dashboard', compact(
            'courses',
            'studentsCount',
            'lessonsTotal',
            'quizzesTotal',
            'assignmentsTotal',
            'quizPercent',
            'assignPercent',
            'pendingGrading',
            'sidebarCounts'
        ))->with('topbarUnread', $unread);
    }
}