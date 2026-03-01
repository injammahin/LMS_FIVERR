<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\TeacherCounts;
use App\Models\Course;
use App\Models\User;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Route;


class CourseController extends Controller
{
    use TeacherCounts;

    private function teacherOwnsCourse(Course $course): void
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();
        abort_if(!$teacher->coursesTeaching()->where('courses.id', $course->id)->exists(), 403);
    }

    public function index()
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        $courses = $teacher->coursesTeaching()
            ->with(['subject.division'])
            ->orderBy('title')
            ->get();

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        return view('teacher.courses.index', compact('courses', 'sidebarCounts'))
            ->with('topbarUnread', $unread);
    }

public function show(Course $course)
{
    $this->teacherOwnsCourse($course);

    $course->load(['subject.division', 'lessons' => fn($q)=>$q->orderBy('position'), 'quizzes', 'assignments']);

    $divisionId = optional($course->subject)->division_id;

    // Students in same division (paginate)
    $students = User::where('role', 'student')
        ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
        ->orderBy('name')
        ->paginate(20);

    $studentIds = $students->pluck('id')->all();

    $lessonIds = $course->lessons->pluck('id')->all();
    $quizIds = $course->quizzes->pluck('id')->all();
    $assignmentIds = $course->assignments->pluck('id')->all();

    // Totals for course
    $totals = [
        'lessons' => count($lessonIds),
        'quizzes' => count($quizIds),
        'assignments' => count($assignmentIds),
    ];

    // ✅ Lesson summary per student
    $lessonAgg = [];
    if (!empty($lessonIds) && !empty($studentIds)) {
        $lessonAgg = LessonProgress::whereIn('lesson_id', $lessonIds)
            ->whereIn('user_id', $studentIds)
            ->selectRaw('user_id,
                SUM(completed_at IS NOT NULL) as done,
                SUM(viewed_at IS NOT NULL) as viewed')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');
    }

    // ✅ Quiz summary per student (attempts + distinct quizzes submitted)
    $quizAgg = [];
    if (!empty($quizIds) && !empty($studentIds)) {
        $quizAgg = QuizAttempt::whereIn('quiz_id', $quizIds)
            ->whereIn('user_id', $studentIds)
            ->whereNotNull('submitted_at')
            ->selectRaw('user_id,
                COUNT(*) as attempts_used,
                COUNT(DISTINCT quiz_id) as quizzes_submitted')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');
    }

    // ✅ Assignment summary per student (submissions + distinct assignments submitted)
    $assAgg = [];
    if (!empty($assignmentIds) && !empty($studentIds)) {
        $assAgg = AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
            ->whereIn('user_id', $studentIds)
            ->selectRaw('user_id,
                COUNT(*) as submissions_used,
                COUNT(DISTINCT assignment_id) as assignments_submitted')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');
    }

    // ✅ Build rows for blade
    $studentRows = $students->getCollection()->map(function ($st) use ($lessonAgg, $quizAgg, $assAgg, $totals) {
        $l = $lessonAgg[$st->id] ?? null;
        $q = $quizAgg[$st->id] ?? null;
        $a = $assAgg[$st->id] ?? null;

        $lessonDone = (int)($l->done ?? 0);
        $lessonViewed = (int)($l->viewed ?? 0);

        $quizSubmitted = (int)($q->quizzes_submitted ?? 0);
        $quizAttempts = (int)($q->attempts_used ?? 0);

        $assSubmitted = (int)($a->assignments_submitted ?? 0);
        $assAttempts = (int)($a->submissions_used ?? 0);

        // overall percent
        $totalItems = (int)$totals['lessons'] + (int)$totals['quizzes'] + (int)$totals['assignments'];
        $doneItems = $lessonDone + $quizSubmitted + $assSubmitted;
        $overallPercent = $totalItems > 0 ? (int)round(($doneItems / $totalItems) * 100) : 0;

        return [
            'student' => $st,
            'lesson_done' => $lessonDone,
            'lesson_viewed' => $lessonViewed,
            'quiz_submitted' => $quizSubmitted,
            'quiz_attempts' => $quizAttempts,
            'ass_submitted' => $assSubmitted,
            'ass_attempts' => $assAttempts,
            'overall_percent' => $overallPercent,
        ];
    });

    $students->setCollection($studentRows);

    $sidebarCounts = $this->sidebarCounts();
    $unread = $sidebarCounts['unread'];

    return view('teacher.courses.show', compact('course', 'students', 'totals', 'sidebarCounts'))
        ->with('topbarUnread', $unread);
}
public function studentProgress(Course $course, User $student)
{
    $this->teacherOwnsCourse($course);

    abort_if($student->role !== 'student', 404);

    $course->load(['subject.division', 'lessons' => fn($q)=>$q->orderBy('position'), 'quizzes', 'assignments']);

    // ensure student is in same division as course
    $divisionId = optional($course->subject)->division_id;
    if ($divisionId) {
        abort_if((int)$student->division_id !== (int)$divisionId, 404);
    }

    $lessonIds = $course->lessons->pluck('id')->all();
    $quizIds = $course->quizzes->pluck('id')->all();
    $assignmentIds = $course->assignments->pluck('id')->all();

    // lesson progress map
    $lessonProgress = !empty($lessonIds)
        ? LessonProgress::where('user_id', $student->id)->whereIn('lesson_id', $lessonIds)->get()->keyBy('lesson_id')
        : collect();

    // quiz attempts grouped by quiz
    $quizAttempts = !empty($quizIds)
        ? QuizAttempt::where('user_id', $student->id)->whereIn('quiz_id', $quizIds)->orderByDesc('id')->get()->groupBy('quiz_id')
        : collect();

    // assignment submissions grouped by assignment
    $assignmentSubs = !empty($assignmentIds)
        ? AssignmentSubmission::where('user_id', $student->id)->whereIn('assignment_id', $assignmentIds)->orderByDesc('id')->get()->groupBy('assignment_id')
        : collect();

    $sidebarCounts = $this->sidebarCounts();
    $unread = $sidebarCounts['unread'];

    return view('teacher.courses.student_progress', compact(
        'course',
        'student',
        'lessonProgress',
        'quizAttempts',
        'assignmentSubs',
        'sidebarCounts'
    ))->with('topbarUnread', $unread);
}
}