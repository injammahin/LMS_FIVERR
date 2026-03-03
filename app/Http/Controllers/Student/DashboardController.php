<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\AssignmentSubmission;
class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 🔥 STEP 1: Check promotion FIRST
        $this->checkAutoPromotion($user);
        $user->refresh(); // VERY IMPORTANT

        // 🔥 STEP 2: Load assigned division AFTER promotion
        $assignedDivision = null;
        $assignedSubjectsCount = 0;
        $assignedCoursesCount = 0;

        if ($user->division_id) {
            $assignedDivision = Division::withCount('subjects')
                ->find($user->division_id);

            if ($assignedDivision) {
                $assignedSubjectsCount = (int) $assignedDivision->subjects_count;

                $assignedCoursesCount = \App\Models\Course::whereHas('subject', function ($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                })->count();
            }
        }

        // 🔥 STEP 3: Load divisions
        $divisions = Division::query()
            ->withCount([
                'subjects',
                'subjects as courses_count' => function ($q) {
                    $q->join('courses', 'courses.subject_id', '=', 'subjects.id');
                }
            ])
            ->orderBy('level')
            ->get();

        // 🔥 STEP 4: Calculate progress AFTER promotion
        $divisionProgress = [];

        foreach ($divisions as $division) {
            $divisionProgress[$division->id] =
                $this->calculateDivisionProgress($user, $division);
        }

        return view('student.dashboard', compact(
            'user',
            'divisions',
            'assignedDivision',
            'assignedSubjectsCount',
            'assignedCoursesCount',
            'divisionProgress'
        ));
    }
    private function checkAutoPromotion($user)
    {
        $currentDivision = Division::find($user->division_id);
        if (!$currentDivision || !$currentDivision->auto_promote) return;

        // calculate completion %
        $stats = $this->calculateDivisionProgress($user, $currentDivision);

        if ($stats['percent'] >= $currentDivision->promotion_percent) {

            // find next level division
            $nextDivision = Division::where('level', '>', $currentDivision->level)
                ->orderBy('level')
                ->first();

            if ($nextDivision) {
                $user->division_id = $nextDivision->id;
                $user->save();
            }
        }
    }
    private function calculateDivisionProgress($user, Division $division)
    {
        $lessonIds = [];
        $quizIds = [];
        $assignmentIds = [];

        $subjects = $division->subjects()
            ->with('courses.lessons', 'courses.quizzes', 'courses.assignments')
            ->get();

        foreach ($subjects as $sub) {
            foreach ($sub->courses as $c) {
                foreach ($c->lessons as $l) $lessonIds[] = $l->id;
                foreach ($c->quizzes as $qz) $quizIds[] = $qz->id;
                foreach ($c->assignments as $a) $assignmentIds[] = $a->id;
            }
        }

        $lessonDone = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->count();

        $quizDone = QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds)
            ->whereNotNull('submitted_at')
            ->distinct('quiz_id')
            ->count('quiz_id');

        $assignmentDone = AssignmentSubmission::where('user_id', $user->id)
            ->whereIn('assignment_id', $assignmentIds)
            ->distinct('assignment_id')
            ->count('assignment_id');

        $total = count($lessonIds) + count($quizIds) + count($assignmentIds);
        $done = $lessonDone + $quizDone + $assignmentDone;

        $percent = $total > 0 ? round(($done / $total) * 100) : 0;

        return [
            'total' => $total,
            'done' => $done,
            'percent' => $percent,
        ];
    }
    public function division(Division $division)
    {
        $user = auth()->user();
        $userDivision = Division::find($user->division_id);

        abort_if(!$userDivision, 403);

        // allow access if requested division level <= user level
        abort_if($division->level > $userDivision->level, 403);

        // Load subjects + courses + items (for cards + activity boxes)
        $subjects = $division->subjects()
            ->withCount('courses')
            ->with([
                'courses' => function ($q) {
                    $q->select('id', 'subject_id', 'title')
                      ->orderBy('title')
                      ->with([
                          'lessons:id,course_id,title,position',
                          'quizzes:id,course_id,title,pass_mark,max_attempts',
                          'assignments:id,course_id,title,max_attempts',
                      ]);
                }
            ])
            ->orderBy('name')
            ->get();

        /**
         * ======================================================
         * Build big ID lists for ONE QUERY per table (fast)
         * ======================================================
         */
        $lessonIds = [];
        $quizIds = [];
        $assignmentIds = [];

        foreach ($subjects as $sub) {
            foreach ($sub->courses as $c) {
                foreach ($c->lessons as $l) $lessonIds[] = $l->id;
                foreach ($c->quizzes as $qz) $quizIds[] = $qz->id;
                foreach ($c->assignments as $a) $assignmentIds[] = $a->id;
            }
        }

        // Remove duplicates
        $lessonIds = array_values(array_unique($lessonIds));
        $quizIds = array_values(array_unique($quizIds));
        $assignmentIds = array_values(array_unique($assignmentIds));

        /**
         * ======================================================
         * Progress maps
         * ======================================================
         */
        // Lesson done map (completed_at)
        $lessonDoneMap = []; // lesson_id => true
        if (!empty($lessonIds)) {
            $lessonDoneMap = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')
                ->flip()
                ->toArray();
        }

        // Quiz submitted map (at least 1 submitted attempt)
        $quizDoneMap = []; // quiz_id => true
        if (!empty($quizIds)) {
            $quizDoneMap = QuizAttempt::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizIds)
                ->whereNotNull('submitted_at')
                ->pluck('quiz_id')
                ->unique()
                ->flip()
                ->toArray();
        }

        // Assignment submitted map (at least 1 submission)
        $assignmentDoneMap = []; // assignment_id => true
        if (!empty($assignmentIds)) {
            $assignmentDoneMap = AssignmentSubmission::where('user_id', $user->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->pluck('assignment_id')
                ->unique()
                ->flip()
                ->toArray();
        }

        /**
         * ======================================================
         * TOP DONUTS (overall division)
         * ======================================================
         */
        $divisionLessonTotal = count($lessonIds);
        $divisionLessonDone  = count($lessonDoneMap);

        $divisionQuizTotal = count($quizIds);
        $divisionQuizDone  = count($quizDoneMap);

        $divisionAssignmentTotal = count($assignmentIds);
        $divisionAssignmentDone  = count($assignmentDoneMap);

        $divisionStats = [
            'lessons_total' => $divisionLessonTotal,
            'lessons_done' => $divisionLessonDone,
            'lessons_percent' => $divisionLessonTotal > 0 ? round(($divisionLessonDone / $divisionLessonTotal) * 100) : 0,

            'quizzes_total' => $divisionQuizTotal,
            'quizzes_done' => $divisionQuizDone,
            'quizzes_percent' => $divisionQuizTotal > 0 ? round(($divisionQuizDone / $divisionQuizTotal) * 100) : 0,

            'assignments_total' => $divisionAssignmentTotal,
            'assignments_done' => $divisionAssignmentDone,
            'assignments_percent' => $divisionAssignmentTotal > 0 ? round(($divisionAssignmentDone / $divisionAssignmentTotal) * 100) : 0,
        ];

        /**
         * ======================================================
         * SUBJECT CARDS:
         * - progress percent
         * - activity tiles (small boxes)
         * - tooltip details
         * ======================================================
         */
        $subjectStats = [];      // subject_id => percent + counts
        $subjectActivities = []; // subject_id => list items for tooltip + tiles

        foreach ($subjects as $sub) {
            $subLessonTotal = 0; $subLessonDone = 0;
            $subQuizTotal = 0;   $subQuizDone = 0;
            $subAssignTotal = 0; $subAssignDone = 0;

            $activities = []; // list of items for tooltip/tiles

            foreach ($sub->courses as $course) {

                // Lessons (ordered)
                $lessons = $course->lessons->sortBy('position')->values();
                foreach ($lessons as $l) {
                    $subLessonTotal++;
                    $done = isset($lessonDoneMap[$l->id]);
                    if ($done) $subLessonDone++;

                    $activities[] = [
                        'type' => 'lesson',
                        'title' => $l->title,
                        'done' => $done,
                        'course_id' => $course->id,
                        'id' => $l->id,
                    ];
                }

                // Quizzes
                foreach ($course->quizzes as $qz) {
                    $subQuizTotal++;
                    $done = isset($quizDoneMap[$qz->id]);
                    if ($done) $subQuizDone++;

                    $activities[] = [
                        'type' => 'quiz',
                        'title' => $qz->title,
                        'done' => $done,
                        'course_id' => $course->id,
                        'id' => $qz->id,
                    ];
                }

                // Assignments
                foreach ($course->assignments as $a) {
                    $subAssignTotal++;
                    $done = isset($assignmentDoneMap[$a->id]);
                    if ($done) $subAssignDone++;

                    $activities[] = [
                        'type' => 'assignment',
                        'title' => $a->title,
                        'done' => $done,
                        'course_id' => $course->id,
                        'id' => $a->id,
                    ];
                }
            }

            // overall percent for subject (lessons+quizzes+assignments)
            $subTotal = $subLessonTotal + $subQuizTotal + $subAssignTotal;
            $subDone  = $subLessonDone + $subQuizDone + $subAssignDone;
            $subPercent = $subTotal > 0 ? round(($subDone / $subTotal) * 100) : 0;

            $subjectStats[$sub->id] = [
                'total' => $subTotal,
                'done' => $subDone,
                'percent' => $subPercent,

                'lessons_total' => $subLessonTotal,
                'lessons_done' => $subLessonDone,
                'quizzes_total' => $subQuizTotal,
                'quizzes_done' => $subQuizDone,
                'assignments_total' => $subAssignTotal,
                'assignments_done' => $subAssignDone,
            ];

            // ✅ show only first 12 boxes (nice UI). Tooltip will show up to 20.
            $subjectActivities[$sub->id] = [
                'tiles' => array_slice($activities, 0, 12),
                'tooltip' => array_slice($activities, 0, 20),
            ];
        }

        return view('student.division', compact(
            'division',
            'subjects',
            'divisionStats',
            'subjectStats',
            'subjectActivities'
        ));
    }
}