<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $staff */
        $staff = auth()->user();

        $courseIds = $staff->coursesSupporting()->pluck('courses.id')->toArray();

        $courses = Course::with(['subject.division'])
            ->whereIn('id', $courseIds)
            ->orderBy('title')
            ->get();

        // students in divisions of those courses
        $divisionIds = $courses->pluck('subject.division_id')->filter()->unique()->values()->all();
        $studentsCount = User::where('role', 'student')
            ->whereIn('division_id', $divisionIds)
            ->count();

        // totals
        $lessonsTotal = Lesson::whereIn('course_id', $courseIds)->count();
        $quizzesTotal = Quiz::whereIn('course_id', $courseIds)->count();
        $assignmentsTotal = \App\Models\Assignment::whereIn('course_id', $courseIds)->count();

        // pending (monitor only)
        $pendingQuizAttempts = QuizAttempt::whereHas('quiz', fn($q) => $q->whereIn('course_id', $courseIds))
            ->whereIn('status', ['submitted', 'reviewed'])
            ->count();

        $pendingAssignmentSubs = AssignmentSubmission::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->whereIn('status', ['submitted'])
            ->count();

        $pendingGrading = $pendingQuizAttempts + $pendingAssignmentSubs;

        // recent lists
        $recentQuizAttempts = QuizAttempt::with(['user','quiz.course'])
            ->whereHas('quiz', fn($q) => $q->whereIn('course_id', $courseIds))
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->take(6)
            ->get();

        $recentAssignmentSubs = AssignmentSubmission::with(['user','assignment.course'])
            ->whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->latest()
            ->take(6)
            ->get();

        // sidebar counts
        $unread = method_exists($staff, 'unreadNotifications') ? $staff->unreadNotifications()->count() : 0;

        $sidebarCounts = [
            'courses' => count($courseIds),
            'pending' => $pendingGrading,
            'unread' => $unread,
        ];

        // simple avg percent (no heavy progress calculation)
        $avgOverallPercent = 0;

        return view('staff.dashboard', compact(
            'courses',
            'studentsCount',
            'pendingGrading',
            'unread',
            'lessonsTotal',
            'quizzesTotal',
            'assignmentsTotal',
            'recentQuizAttempts',
            'recentAssignmentSubs',
            'avgOverallPercent',
            'sidebarCounts'
        ))->with('topbarUnread', $unread);
    }
}