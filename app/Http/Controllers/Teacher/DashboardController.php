<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\TeacherCounts;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use TeacherCounts;

    public function index()
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        $courses = $teacher->coursesTeaching()
            ->with(['subject.division'])
            ->orderBy('title')
            ->get();

        $courseIds = $courses->pluck('id')->all();

        $divisionIds = $courses->pluck('subject.division_id')->filter()->unique()->values()->all();

        // Students by division
        $studentsByDivision = User::where('role', 'student')
            ->when(!empty($divisionIds), fn($q) => $q->whereIn('division_id', $divisionIds))
            ->selectRaw('division_id, COUNT(*) as cnt')
            ->groupBy('division_id')
            ->pluck('cnt', 'division_id')
            ->toArray();

        $studentsCount = array_sum($studentsByDivision);

        // Totals by course
        $lessonsByCourse = Lesson::whereIn('course_id', $courseIds)
            ->selectRaw('course_id, COUNT(*) as cnt')
            ->groupBy('course_id')
            ->pluck('cnt', 'course_id')
            ->toArray();

        $quizzesByCourse = Quiz::whereIn('course_id', $courseIds)
            ->selectRaw('course_id, COUNT(*) as cnt')
            ->groupBy('course_id')
            ->pluck('cnt', 'course_id')
            ->toArray();

        $assignByCourse = Assignment::whereIn('course_id', $courseIds)
            ->selectRaw('course_id, COUNT(*) as cnt')
            ->groupBy('course_id')
            ->pluck('cnt', 'course_id')
            ->toArray();

        $lessonsTotal = array_sum($lessonsByCourse);
        $quizzesTotal = array_sum($quizzesByCourse);
        $assignmentsTotal = array_sum($assignByCourse);

        // Student IDs (all students in relevant divisions)
        $studentIds = User::where('role', 'student')
            ->when(!empty($divisionIds), fn($q) => $q->whereIn('division_id', $divisionIds))
            ->pluck('id')
            ->all();

        // ✅ Lesson completed counts per course (across all students)
        $lessonDoneByCourse = [];
        if (!empty($courseIds) && !empty($studentIds)) {
            $lessonDoneByCourse = LessonProgress::query()
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->whereIn('lessons.course_id', $courseIds)
                ->whereIn('lesson_progress.user_id', $studentIds)
                ->whereNotNull('lesson_progress.completed_at')
                ->selectRaw('lessons.course_id as course_id, COUNT(*) as cnt')
                ->groupBy('lessons.course_id')
                ->pluck('cnt', 'course_id')
                ->toArray();
        }

        // ✅ Quiz “done” counts per course = distinct (user, quiz) submitted
        $quizDoneByCourse = [];
        if (!empty($courseIds) && !empty($studentIds)) {
            $quizDoneByCourse = QuizAttempt::query()
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->whereIn('quizzes.course_id', $courseIds)
                ->whereIn('quiz_attempts.user_id', $studentIds)
                ->whereNotNull('quiz_attempts.submitted_at')
                ->selectRaw("quizzes.course_id as course_id, COUNT(DISTINCT CONCAT(quiz_attempts.user_id,'-',quiz_attempts.quiz_id)) as cnt")
                ->groupBy('quizzes.course_id')
                ->pluck('cnt', 'course_id')
                ->toArray();
        }

        // ✅ Assignment “done” counts per course = distinct (user, assignment) submitted
        $assignDoneByCourse = [];
        if (!empty($courseIds) && !empty($studentIds)) {
            $assignDoneByCourse = AssignmentSubmission::query()
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->whereIn('assignments.course_id', $courseIds)
                ->whereIn('assignment_submissions.user_id', $studentIds)
                ->selectRaw("assignments.course_id as course_id, COUNT(DISTINCT CONCAT(assignment_submissions.user_id,'-',assignment_submissions.assignment_id)) as cnt")
                ->groupBy('assignments.course_id')
                ->pluck('cnt', 'course_id')
                ->toArray();
        }

        // ✅ For top donuts (distinct quizzes/assignments that have any submission)
        $quizIds = Quiz::whereIn('course_id', $courseIds)->pluck('id')->all();
        $assignmentIds = Assignment::whereIn('course_id', $courseIds)->pluck('id')->all();

        $quizSubmittedCount = !empty($quizIds)
            ? QuizAttempt::whereIn('quiz_id', $quizIds)
                ->whereNotNull('submitted_at')
                ->distinct()
                ->count('quiz_id')
            : 0;

        $assignmentSubmittedCount = !empty($assignmentIds)
            ? AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
                ->distinct()
                ->count('assignment_id')
            : 0;

        $quizPercent   = $quizzesTotal > 0 ? round(($quizSubmittedCount / $quizzesTotal) * 100) : 0;
        $assignPercent = $assignmentsTotal > 0 ? round(($assignmentSubmittedCount / $assignmentsTotal) * 100) : 0;

        // Pending grading totals (existing logic)
        $pendingGrading = $this->pendingCount($courseIds);

        // ✅ Build course insights (average progress per course)
        $courseInsights = $courses->map(function ($course) use (
            $studentsByDivision,
            $lessonsByCourse,
            $quizzesByCourse,
            $assignByCourse,
            $lessonDoneByCourse,
            $quizDoneByCourse,
            $assignDoneByCourse
        ) {
            $divisionId = optional($course->subject)->division_id;
            $studentCount = (int)($studentsByDivision[$divisionId] ?? 0);

            $lTotal = (int)($lessonsByCourse[$course->id] ?? 0);
            $qTotal = (int)($quizzesByCourse[$course->id] ?? 0);
            $aTotal = (int)($assignByCourse[$course->id] ?? 0);

            $lDone = (int)($lessonDoneByCourse[$course->id] ?? 0);
            $qDone = (int)($quizDoneByCourse[$course->id] ?? 0);
            $aDone = (int)($assignDoneByCourse[$course->id] ?? 0);

            // possible completions = items * students
            $lPossible = $studentCount > 0 ? $lTotal * $studentCount : 0;
            $qPossible = $studentCount > 0 ? $qTotal * $studentCount : 0;
            $aPossible = $studentCount > 0 ? $aTotal * $studentCount : 0;

            $lPercent = $lPossible > 0 ? round(($lDone / $lPossible) * 100) : 0;
            $qPercent = $qPossible > 0 ? round(($qDone / $qPossible) * 100) : 0;
            $aPercent = $aPossible > 0 ? round(($aDone / $aPossible) * 100) : 0;

            $overallPossible = $lPossible + $qPossible + $aPossible;
            $overallDone = $lDone + $qDone + $aDone;
            $overallPercent = $overallPossible > 0 ? round(($overallDone / $overallPossible) * 100) : 0;

            return [
                'course' => $course,
                'students' => $studentCount,
                'lessons_total' => $lTotal,
                'quizzes_total' => $qTotal,
                'assignments_total' => $aTotal,
                'lesson_percent' => $lPercent,
                'quiz_percent' => $qPercent,
                'assignment_percent' => $aPercent,
                'overall_percent' => $overallPercent,
            ];
        })->values();

        $avgOverallPercent = $courseInsights->count()
            ? (int) round($courseInsights->avg('overall_percent'))
            : 0;

        // ✅ Recent activity (latest submissions)
        $recentQuizAttempts = QuizAttempt::query()
            ->with(['user', 'quiz.course'])
            ->whereHas('quiz.course', fn($q) => $q->whereIn('id', $courseIds))
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->take(8)
            ->get();

        $recentAssignmentSubs = AssignmentSubmission::query()
            ->with(['user', 'assignment.course'])
            ->whereHas('assignment.course', fn($q) => $q->whereIn('id', $courseIds))
            ->latest()
            ->take(8)
            ->get();

        // ✅ Pending lists (latest)
        $pendingQuizAttempts = QuizAttempt::query()
            ->with(['user', 'quiz.course'])
            ->whereHas('quiz.course', fn($q) => $q->whereIn('id', $courseIds))
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->take(8)
            ->get();

        $pendingAssignmentSubs = AssignmentSubmission::query()
            ->with(['user', 'assignment.course'])
            ->whereHas('assignment.course', fn($q) => $q->whereIn('id', $courseIds))
            ->where('status', 'submitted')
            ->latest()
            ->take(8)
            ->get();

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        return view('teacher.dashboard', compact(
            'courses',
            'studentsCount',
            'lessonsTotal',
            'quizzesTotal',
            'assignmentsTotal',
            'pendingGrading',
            'sidebarCounts',
            'unread',
            'quizSubmittedCount',
            'assignmentSubmittedCount',
            'quizPercent',
            'assignPercent',
            'courseInsights',
            'avgOverallPercent',
            'recentQuizAttempts',
            'recentAssignmentSubs',
            'pendingQuizAttempts',
            'pendingAssignmentSubs'
        ))->with('topbarUnread', $unread);
    }
}