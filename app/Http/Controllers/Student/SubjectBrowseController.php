<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Subject;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\AssignmentSubmission;

class SubjectBrowseController extends Controller
{
    public function show(Division $division, Subject $subject)
    {
        $user = auth()->user();

        // Must be assigned division
        $userDivision = Division::find($user->division_id);

        abort_if(!$userDivision, 403);

        // allow access if requested division level <= user level
        abort_if($division->level > $userDivision->level, 403);

        // Subject must belong to this division
        abort_if((int)$subject->division_id !== (int)$division->id, 404);

        // Load courses and their lessons/quizzes/assignments
        $subject->load([
            'courses' => function ($q) {
                $q->where('status', 'published')
                ->orderBy('title')
                ->with([
                    'lessons' => fn($lq) => $lq->orderBy('position'),

                    // ✅ ONLY published quizzes
                    'quizzes' => fn($qq) =>
                        $qq->where('status','published')->latest(),

                    'assignments' => fn($aq) => $aq->latest(),
                ]);
            }
        ]);

        // Counts
        $coursesCount = $subject->courses->count();
        $lessonsCount = $subject->courses->sum(fn($c) => $c->lessons->count());
        $quizzesCount = $subject->courses->sum(fn($c) => $c->quizzes->count());
        $assignmentsCount = $subject->courses->sum(fn($c) => $c->assignments->count());

        /**
         * =========================================================
         * ✅ LESSON PROGRESS (Not started / Viewed / Done)
         * =========================================================
         */
        $lessonIds = $subject->courses
            ->flatMap(fn($c) => $c->lessons->pluck('id'))
            ->values()
            ->all();

        $progressMap = [];
        if (!empty($lessonIds)) {
            $progressMap = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->get()
                ->keyBy('lesson_id');
        }

        // Per-course lesson progress (used by your old bar)
        $courseProgress = [];
        foreach ($subject->courses as $course) {
            $totalLessons = $course->lessons->count();

            $doneLessons = $course->lessons->filter(function ($lesson) use ($progressMap) {
                $p = $progressMap[$lesson->id] ?? null;
                return !empty($p?->completed_at);
            })->count();

            $percent = $totalLessons > 0 ? round(($doneLessons / $totalLessons) * 100) : 0;

            $courseProgress[$course->id] = [
                'total' => $totalLessons,
                'done' => $doneLessons,
                'percent' => $percent,
            ];
        }

        /**
         * =========================================================
         * ✅ QUIZ STATUS + ATTEMPTS
         * =========================================================
         */
        $quizIds = $subject->courses
            ->flatMap(fn($c) => $c->quizzes->pluck('id'))
            ->values()
            ->all();

        // quizAttemptSummary[quiz_id] = ['used' => int, 'status' => string, 'last' => QuizAttempt|null]
        $quizAttemptSummary = [];

            if (!empty($quizIds)) {
                $attempts = QuizAttempt::where('user_id', $user->id)
                    ->whereIn('quiz_id', $quizIds)
                    ->orderByDesc('id')
                    ->get()
                    ->groupBy('quiz_id');

                foreach ($attempts as $quizId => $rows) {
                    $used = $rows->whereNotNull('submitted_at')->count();

                    $last = $rows->first(); // latest row

                    // ✅ latest submitted attempt (what we need for Result button / pass-fail)
                    $lastSubmitted = $rows->first(fn($a) => !is_null($a->submitted_at));

                    $status = 'not_started';
                    if ($rows->contains('status', 'in_progress')) {
                        $status = 'in_progress';
                    } elseif ($used > 0) {
                        $status = 'submitted';
                    }

                    $quizAttemptSummary[$quizId] = [
                        'used' => $used,
                        'last' => $last,
                        'last_submitted' => $lastSubmitted,
                        'status' => $status,
                    ];
                }
            }

        /**
         * =========================================================
         * ✅ ASSIGNMENT SUBMISSION STATUS
         * =========================================================
         */
        $assignmentIds = $subject->courses
            ->flatMap(fn($c) => $c->assignments->pluck('id'))
            ->values()
            ->all();

        // assignmentSubmissionSummary[assignment_id] = ['used' => int, 'last' => AssignmentSubmission|null, 'status' => string]
        $assignmentSubmissionSummary = [];

        if (!empty($assignmentIds)) {
            $subs = AssignmentSubmission::where('user_id', $user->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->orderByDesc('id')
                ->get()
                ->groupBy('assignment_id');

            foreach ($subs as $assignmentId => $rows) {
                $used = $rows->count();
                $last = $rows->first(); // latest due to desc

                $status = 'not_submitted';
                if ($used > 0) $status = 'submitted';
                if (($last?->status ?? '') === 'graded') $status = 'graded';

                $assignmentSubmissionSummary[$assignmentId] = [
                    'used' => $used,
                    'last' => $last,
                    'status' => $status,
                ];
            }
        }

        /**
         * =========================================================
         * ✅ PER-COURSE FULL STATS (for donut + 3 bars)
         * =========================================================
         */
        $courseStats = [];

        foreach ($subject->courses as $course) {

            // Lessons
            $lessonTotal = $course->lessons->count();
            $lessonDone = $course->lessons->filter(function ($lesson) use ($progressMap) {
                $p = $progressMap[$lesson->id] ?? null;
                return !empty($p?->completed_at);
            })->count();

            // Quizzes (done if at least 1 submitted attempt)
            $quizTotal = $course->quizzes->count();
            $quizDone = $course->quizzes->filter(function ($quiz) use ($quizAttemptSummary) {
                $sum = $quizAttemptSummary[$quiz->id] ?? null;
                $used = (int)($sum['used'] ?? 0);
                return $used > 0;
            })->count();

            // Assignments (done if at least 1 submission)
            $assignmentTotal = $course->assignments->count();
            $assignmentDone = $course->assignments->filter(function ($assignment) use ($assignmentSubmissionSummary) {
                $sum = $assignmentSubmissionSummary[$assignment->id] ?? null;
                $used = (int)($sum['used'] ?? 0);
                return $used > 0;
            })->count();

            // Overall
            $overallTotal = $lessonTotal + $quizTotal + $assignmentTotal;
            $overallDone = $lessonDone + $quizDone + $assignmentDone;
            $overallPercent = $overallTotal > 0 ? round(($overallDone / $overallTotal) * 100) : 0;

            $courseStats[$course->id] = [
                'lesson_total' => $lessonTotal,
                'lesson_done' => $lessonDone,
                'lesson_percent' => $lessonTotal > 0 ? round(($lessonDone / $lessonTotal) * 100) : 0,

                'quiz_total' => $quizTotal,
                'quiz_done' => $quizDone,
                'quiz_percent' => $quizTotal > 0 ? round(($quizDone / $quizTotal) * 100) : 0,

                'assignment_total' => $assignmentTotal,
                'assignment_done' => $assignmentDone,
                'assignment_percent' => $assignmentTotal > 0 ? round(($assignmentDone / $assignmentTotal) * 100) : 0,

                'overall_total' => $overallTotal,
                'overall_done' => $overallDone,
                'overall_percent' => $overallPercent,
            ];
        }

        return view('student.subject', compact(
            'division',
            'subject',
            'coursesCount',
            'lessonsCount',
            'quizzesCount',
            'assignmentsCount',
            'progressMap',
            'courseProgress',
            'quizAttemptSummary',
            'assignmentSubmissionSummary',
            'courseStats'
        ));
    }
}