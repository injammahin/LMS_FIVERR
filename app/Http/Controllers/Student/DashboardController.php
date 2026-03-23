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

        // check promotion first
        $promotionCelebration = $this->checkAutoPromotion($user);

        // refresh user after possible promotion
        $user->refresh();

        $assignedDivision = null;
        $assignedSubjectsCount = 0;
        $assignedCoursesCount = 0;

        if ($user->division_id) {
            $assignedDivision = Division::withCount('subjects')->find($user->division_id);

            if ($assignedDivision) {
                $assignedSubjectsCount = (int) $assignedDivision->subjects_count;

                $assignedCoursesCount = \App\Models\Course::whereHas('subject', function ($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                })->count();
            }
        }

        $divisions = Division::query()
            ->withCount([
                'subjects',
                'subjects as courses_count' => function ($q) {
                    $q->join('courses', 'courses.subject_id', '=', 'subjects.id');
                }
            ])
            ->orderBy('level')
            ->get();

        $divisionProgress = [];

        foreach ($divisions as $division) {
            $divisionProgress[$division->id] = $this->calculateDivisionProgress($user, $division);
        }

        // one-time flash
        if (!empty($promotionCelebration['show'])) {
            session()->flash('division_unlock_celebration', $promotionCelebration);
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

    private function checkAutoPromotion($user): array
    {
        $currentDivision = Division::find($user->division_id);

        if (!$currentDivision || !$currentDivision->auto_promote) {
            return ['show' => false];
        }

        $stats = $this->calculateDivisionProgress($user, $currentDivision);

        if ($stats['percent'] < (int) $currentDivision->promotion_percent) {
            return ['show' => false];
        }

        $nextDivision = Division::where('level', '>', $currentDivision->level)
            ->orderBy('level')
            ->first();

        if (!$nextDivision) {
            return ['show' => false];
        }

        // if already promoted, do not show again
        if ((int) $user->division_id === (int) $nextDivision->id) {
            return ['show' => false];
        }

        $fromDivisionId = (int) $currentDivision->id;
        $toDivisionId = (int) $nextDivision->id;

        $user->division_id = $toDivisionId;
        $user->save();

        return [
            'show' => true,
            'from_division_id' => $fromDivisionId,
            'from_division_name' => $currentDivision->name,
            'to_division_id' => $toDivisionId,
            'to_division_name' => $nextDivision->name,
            'message' => "Amazing! You completed {$currentDivision->name} and unlocked {$nextDivision->name}.",
        ];
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
            foreach ($sub->courses as $course) {
                foreach ($course->lessons as $lesson) {
                    $lessonIds[] = $lesson->id;
                }
                foreach ($course->quizzes as $quiz) {
                    $quizIds[] = $quiz->id;
                }
                foreach ($course->assignments as $assignment) {
                    $assignmentIds[] = $assignment->id;
                }
            }
        }

        $lessonDone = !empty($lessonIds)
            ? LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('completed_at')
                ->count()
            : 0;

        $quizDone = !empty($quizIds)
            ? QuizAttempt::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizIds)
                ->whereNotNull('submitted_at')
                ->distinct('quiz_id')
                ->count('quiz_id')
            : 0;

        $assignmentDone = !empty($assignmentIds)
            ? AssignmentSubmission::where('user_id', $user->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->distinct('assignment_id')
                ->count('assignment_id')
            : 0;

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
        abort_if($division->level > $userDivision->level, 403);

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

        $lessonIds = [];
        $quizIds = [];
        $assignmentIds = [];

        foreach ($subjects as $sub) {
            foreach ($sub->courses as $course) {
                foreach ($course->lessons as $lesson) {
                    $lessonIds[] = $lesson->id;
                }
                foreach ($course->quizzes as $quiz) {
                    $quizIds[] = $quiz->id;
                }
                foreach ($course->assignments as $assignment) {
                    $assignmentIds[] = $assignment->id;
                }
            }
        }

        $lessonIds = array_values(array_unique($lessonIds));
        $quizIds = array_values(array_unique($quizIds));
        $assignmentIds = array_values(array_unique($assignmentIds));

        $lessonDoneMap = [];
        if (!empty($lessonIds)) {
            $lessonDoneMap = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')
                ->flip()
                ->toArray();
        }

        $quizDoneMap = [];
        if (!empty($quizIds)) {
            $quizDoneMap = QuizAttempt::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizIds)
                ->whereNotNull('submitted_at')
                ->pluck('quiz_id')
                ->unique()
                ->flip()
                ->toArray();
        }

        $assignmentDoneMap = [];
        if (!empty($assignmentIds)) {
            $assignmentDoneMap = AssignmentSubmission::where('user_id', $user->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->pluck('assignment_id')
                ->unique()
                ->flip()
                ->toArray();
        }

        $divisionLessonTotal = count($lessonIds);
        $divisionLessonDone = count($lessonDoneMap);

        $divisionQuizTotal = count($quizIds);
        $divisionQuizDone = count($quizDoneMap);

        $divisionAssignmentTotal = count($assignmentIds);
        $divisionAssignmentDone = count($assignmentDoneMap);

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

        $subjectStats = [];
        $subjectActivities = [];

        foreach ($subjects as $sub) {
            $subLessonTotal = 0;
            $subLessonDone = 0;
            $subQuizTotal = 0;
            $subQuizDone = 0;
            $subAssignTotal = 0;
            $subAssignDone = 0;

            $activities = [];

            foreach ($sub->courses as $course) {
                $lessons = $course->lessons->sortBy('position')->values();

                foreach ($lessons as $lesson) {
                    $subLessonTotal++;
                    $done = isset($lessonDoneMap[$lesson->id]);
                    if ($done) {
                        $subLessonDone++;
                    }

                    $activities[] = [
                        'type' => 'lesson',
                        'title' => $lesson->title,
                        'done' => $done,
                        'course_id' => $course->id,
                        'id' => $lesson->id,
                    ];
                }

                foreach ($course->quizzes as $quiz) {
                    $subQuizTotal++;
                    $done = isset($quizDoneMap[$quiz->id]);
                    if ($done) {
                        $subQuizDone++;
                    }

                    $activities[] = [
                        'type' => 'quiz',
                        'title' => $quiz->title,
                        'done' => $done,
                        'course_id' => $course->id,
                        'id' => $quiz->id,
                    ];
                }

                foreach ($course->assignments as $assignment) {
                    $subAssignTotal++;
                    $done = isset($assignmentDoneMap[$assignment->id]);
                    if ($done) {
                        $subAssignDone++;
                    }

                    $activities[] = [
                        'type' => 'assignment',
                        'title' => $assignment->title,
                        'done' => $done,
                        'course_id' => $course->id,
                        'id' => $assignment->id,
                    ];
                }
            }

            $subTotal = $subLessonTotal + $subQuizTotal + $subAssignTotal;
            $subDone = $subLessonDone + $subQuizDone + $subAssignDone;
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