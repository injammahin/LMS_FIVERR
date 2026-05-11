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

        /*
        |--------------------------------------------------------------------------
        | Basic Access Check
        |--------------------------------------------------------------------------
        */
        $userDivision = Division::find($user->division_id);

        abort_if(!$userDivision, 403);

        abort_if($division->level > $userDivision->level, 403);

        abort_if((int) $subject->division_id !== (int) $division->id, 404);

        /*
        |--------------------------------------------------------------------------
        | Load Courses
        |--------------------------------------------------------------------------
        | Important:
        | Do not order by title if your course titles are like:
        | Course 1, Course 2, Course 10.
        |
        | Because title sorting may show:
        | Course 1, Course 10, Course 11, Course 2.
        |
        | If your courses table has a position column, use orderBy('position').
        | If not, use orderBy('id') for now.
        */
        $subject->load([
            'courses' => function ($q) {
                $q->where('status', 'published')
                    ->orderBy('id')
                    ->with([
                        'lessons' => fn ($lq) => $lq->orderBy('position'),

                        'quizzes' => fn ($qq) =>
                            $qq->where('status', 'published')->latest(),

                        'assignments' => fn ($aq) => $aq->latest(),
                    ]);
            }
        ]);

        /*
        |--------------------------------------------------------------------------
        | Apply Course Rule
        |--------------------------------------------------------------------------
        | Assignment: every 5th course
        | Quiz: every 45th course
        |
        | Example:
        | Course 5, 10, 15, 20, 25, 30, 35, 40 = assignment only
        | Course 45 = assignment + quiz
        */
        $subject->courses->values()->each(function ($course, $index) {
            $courseNumber = $index + 1;

            $showAssignment = $courseNumber % 5 === 0;
            $showQuiz = $courseNumber % 45 === 0;

            $course->course_number = $courseNumber;
            $course->show_assignment = $showAssignment;
            $course->show_quiz = $showQuiz;

            if (!$showAssignment) {
                $course->setRelation('assignments', collect());
            }

            if (!$showQuiz) {
                $course->setRelation('quizzes', collect());
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */
        $coursesCount = $subject->courses->count();

        $lessonsCount = $subject->courses->sum(function ($course) {
            return $course->lessons->count();
        });

        $quizzesCount = $subject->courses->sum(function ($course) {
            return $course->quizzes->count();
        });

        $assignmentsCount = $subject->courses->sum(function ($course) {
            return $course->assignments->count();
        });

        /*
        |--------------------------------------------------------------------------
        | Lesson Progress
        |--------------------------------------------------------------------------
        */
        $lessonIds = $subject->courses
            ->flatMap(fn ($course) => $course->lessons->pluck('id'))
            ->values()
            ->all();

        $progressMap = [];

        if (!empty($lessonIds)) {
            $progressMap = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->get()
                ->keyBy('lesson_id');
        }

        $courseProgress = [];

        foreach ($subject->courses as $course) {
            $totalLessons = $course->lessons->count();

            $doneLessons = $course->lessons->filter(function ($lesson) use ($progressMap) {
                $progress = $progressMap[$lesson->id] ?? null;

                return !empty($progress?->completed_at);
            })->count();

            $percent = $totalLessons > 0
                ? round(($doneLessons / $totalLessons) * 100)
                : 0;

            $courseProgress[$course->id] = [
                'total' => $totalLessons,
                'done' => $doneLessons,
                'percent' => $percent,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Quiz Status + Attempts
        |--------------------------------------------------------------------------
        */
        $quizIds = $subject->courses
            ->flatMap(fn ($course) => $course->quizzes->pluck('id'))
            ->values()
            ->all();

        $quizAttemptSummary = [];

        if (!empty($quizIds)) {
            $attempts = QuizAttempt::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizIds)
                ->orderByDesc('id')
                ->get()
                ->groupBy('quiz_id');

            foreach ($attempts as $quizId => $rows) {
                $used = $rows->whereNotNull('submitted_at')->count();

                $last = $rows->first();

                $lastSubmitted = $rows->first(function ($attempt) {
                    return !is_null($attempt->submitted_at);
                });

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

        /*
        |--------------------------------------------------------------------------
        | Assignment Submission Status
        |--------------------------------------------------------------------------
        */
        $assignmentIds = $subject->courses
            ->flatMap(fn ($course) => $course->assignments->pluck('id'))
            ->values()
            ->all();

        $assignmentSubmissionSummary = [];

        if (!empty($assignmentIds)) {
            $submissions = AssignmentSubmission::where('user_id', $user->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->orderByDesc('id')
                ->get()
                ->groupBy('assignment_id');

            foreach ($submissions as $assignmentId => $rows) {
                $used = $rows->count();

                $last = $rows->first();

                $status = 'not_submitted';

                if ($used > 0) {
                    $status = 'submitted';
                }

                if (($last?->status ?? '') === 'graded') {
                    $status = 'graded';
                }

                $assignmentSubmissionSummary[$assignmentId] = [
                    'used' => $used,
                    'last' => $last,
                    'status' => $status,
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Per Course Full Stats
        |--------------------------------------------------------------------------
        */
        $courseStats = [];

        foreach ($subject->courses as $course) {
            $lessonTotal = $course->lessons->count();

            $lessonDone = $course->lessons->filter(function ($lesson) use ($progressMap) {
                $progress = $progressMap[$lesson->id] ?? null;

                return !empty($progress?->completed_at);
            })->count();

            $quizTotal = $course->quizzes->count();

            $quizDone = $course->quizzes->filter(function ($quiz) use ($quizAttemptSummary) {
                $summary = $quizAttemptSummary[$quiz->id] ?? null;

                return (int) ($summary['used'] ?? 0) > 0;
            })->count();

            $assignmentTotal = $course->assignments->count();

            $assignmentDone = $course->assignments->filter(function ($assignment) use ($assignmentSubmissionSummary) {
                $summary = $assignmentSubmissionSummary[$assignment->id] ?? null;

                return (int) ($summary['used'] ?? 0) > 0;
            })->count();

            $overallTotal = $lessonTotal + $quizTotal + $assignmentTotal;
            $overallDone = $lessonDone + $quizDone + $assignmentDone;

            $courseStats[$course->id] = [
                'lesson_total' => $lessonTotal,
                'lesson_done' => $lessonDone,
                'lesson_percent' => $lessonTotal > 0
                    ? round(($lessonDone / $lessonTotal) * 100)
                    : 0,

                'quiz_total' => $quizTotal,
                'quiz_done' => $quizDone,
                'quiz_percent' => $quizTotal > 0
                    ? round(($quizDone / $quizTotal) * 100)
                    : 0,

                'assignment_total' => $assignmentTotal,
                'assignment_done' => $assignmentDone,
                'assignment_percent' => $assignmentTotal > 0
                    ? round(($assignmentDone / $assignmentTotal) * 100)
                    : 0,

                'overall_total' => $overallTotal,
                'overall_done' => $overallDone,
                'overall_percent' => $overallTotal > 0
                    ? round(($overallDone / $overallTotal) * 100)
                    : 0,
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