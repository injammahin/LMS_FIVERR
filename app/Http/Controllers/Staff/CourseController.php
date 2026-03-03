<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseController extends Controller
{
    
    public function index(Request $request)
    {
        $staff = auth()->user();
        /** @var \App\Models\User $staff */
        $courses = $staff->coursesSupporting()
            ->with(['subject.division'])
            ->withCount(['lessons', 'quizzes', 'assignments'])
            ->orderBy('title')
            ->paginate(12)
            ->appends($request->query()); // ✅ instead of withQueryString()

        return view('staff.courses.index', compact('courses'));
    }

    public function show(Request $request, Course $course)
    {
        /** @var \App\Models\User $staff */
        $staff = auth()->user();

        abort_if(!$staff->coursesSupporting()->where('courses.id', $course->id)->exists(), 403);

        $course->load(['subject.division']);

        // ✅ totals
        $totals = [
            'lessons' => Lesson::where('course_id', $course->id)->count(),
            'quizzes' => Quiz::where('course_id', $course->id)->count(),
            'assignments' => Assignment::where('course_id', $course->id)->count(),
        ];

        // ✅ students in this course division
        $divisionId = optional($course->subject)->division_id;

        $studentsPaginator = User::query()
            ->where('role', 'student')
            ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        $studentModels = $studentsPaginator->getCollection();
        $userIds = $studentModels->pluck('id')->all();

        // If no students, return empty rows but keep paginator
        if (empty($userIds)) {
            $students = $studentsPaginator->setCollection(collect());
            return view('staff.courses.show', compact('course', 'totals', 'students'));
        }

        // ✅ lesson done + viewed
        $lessonDone = collect();
        $lessonViewed = collect();

        if (Schema::hasTable('lesson_progress') && Schema::hasTable('lessons')) {
            $lessonDone = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->where('lessons.course_id', $course->id)
                ->whereIn('lesson_progress.user_id', $userIds)
                ->whereNotNull('lesson_progress.completed_at')
                ->select('lesson_progress.user_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('lesson_progress.user_id')
                ->pluck('cnt', 'lesson_progress.user_id');

            $lessonViewed = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->where('lessons.course_id', $course->id)
                ->whereIn('lesson_progress.user_id', $userIds)
                ->whereNotNull('lesson_progress.viewed_at')
                ->select('lesson_progress.user_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('lesson_progress.user_id')
                ->pluck('cnt', 'lesson_progress.user_id');
        }

        // ✅ quiz submitted (distinct quiz_id) + attempts (count)
        $quizSubmitted = collect();
        $quizAttempts = collect();

        if (Schema::hasTable('quiz_attempts') && Schema::hasTable('quizzes')) {
            $quizSubmitted = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->where('quizzes.course_id', $course->id)
                ->whereIn('quiz_attempts.user_id', $userIds)
                ->whereNotNull('quiz_attempts.submitted_at')
                ->select('quiz_attempts.user_id', DB::raw('COUNT(DISTINCT quiz_attempts.quiz_id) as cnt'))
                ->groupBy('quiz_attempts.user_id')
                ->pluck('cnt', 'quiz_attempts.user_id');

            $quizAttempts = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->where('quizzes.course_id', $course->id)
                ->whereIn('quiz_attempts.user_id', $userIds)
                ->select('quiz_attempts.user_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('quiz_attempts.user_id')
                ->pluck('cnt', 'quiz_attempts.user_id');
        }

        // ✅ assignment submitted (distinct assignment_id) + submissions count
        $assSubmitted = collect();
        $assAttempts = collect();

        if (Schema::hasTable('assignment_submissions') && Schema::hasTable('assignments')) {
            $assSubmitted = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->where('assignments.course_id', $course->id)
                ->whereIn('assignment_submissions.user_id', $userIds)
                ->select('assignment_submissions.user_id', DB::raw('COUNT(DISTINCT assignment_submissions.assignment_id) as cnt'))
                ->groupBy('assignment_submissions.user_id')
                ->pluck('cnt', 'assignment_submissions.user_id');

            $assAttempts = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->where('assignments.course_id', $course->id)
                ->whereIn('assignment_submissions.user_id', $userIds)
                ->select('assignment_submissions.user_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('assignment_submissions.user_id')
                ->pluck('cnt', 'assignment_submissions.user_id');
        }

        // ✅ build rows exactly like your blade expects
        $overallTotalItems = (int)$totals['lessons'] + (int)$totals['quizzes'] + (int)$totals['assignments'];

        $rows = $studentModels->map(function ($st) use (
            $lessonDone, $lessonViewed, $quizSubmitted, $quizAttempts,
            $assSubmitted, $assAttempts, $overallTotalItems
        ) {
            $ld = (int)($lessonDone[$st->id] ?? 0);
            $lv = (int)($lessonViewed[$st->id] ?? 0);

            $qd = (int)($quizSubmitted[$st->id] ?? 0);
            $qa = (int)($quizAttempts[$st->id] ?? 0);

            $ad = (int)($assSubmitted[$st->id] ?? 0);
            $aa = (int)($assAttempts[$st->id] ?? 0);

            $done = $ld + $qd + $ad;
            $overallPercent = $overallTotalItems > 0 ? (int)round(($done / $overallTotalItems) * 100) : 0;

            return [
                'student' => $st,
                'lesson_done' => $ld,
                'lesson_viewed' => $lv,
                'quiz_submitted' => $qd,
                'quiz_attempts' => $qa,
                'ass_submitted' => $ad,
                'ass_attempts' => $aa,
                'overall_percent' => $overallPercent,
            ];
        });

        // ✅ IMPORTANT: replace paginator collection with $rows (so blade can foreach $students)
        $students = $studentsPaginator->setCollection($rows);

        return view('staff.courses.show', compact('course', 'totals', 'students'));
    }
    public function studentProgress(Course $course, User $student)
    {
        /** @var \App\Models\User $staff */
        $staff = auth()->user();

        // ✅ staff must have this course assigned
        abort_if(!$staff->coursesSupporting()->where('courses.id', $course->id)->exists(), 403);

        $course->load(['subject.division', 'lessons', 'quizzes', 'assignments']);

        // ✅ student must be in same division as the course division (recommended)
        $divisionId = optional($course->subject)->division_id;
        abort_if($divisionId && (int)$student->division_id !== (int)$divisionId, 404);

        $lessonIds = $course->lessons->pluck('id')->all();
        $quizIds = $course->quizzes->pluck('id')->all();
        $assignmentIds = $course->assignments->pluck('id')->all();

        // ✅ lesson progress (keyed by lesson_id)
        $lessonProgress = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('lesson_progress') && !empty($lessonIds)) {
            $lessonProgress = \Illuminate\Support\Facades\DB::table('lesson_progress')
                ->where('user_id', $student->id)
                ->whereIn('lesson_id', $lessonIds)
                ->get()
                ->keyBy('lesson_id');
        }

        // ✅ quiz attempts (grouped by quiz_id)
        $quizAttempts = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('quiz_attempts') && !empty($quizIds)) {
            $quizAttempts = \App\Models\QuizAttempt::query()
                ->where('user_id', $student->id)
                ->whereIn('quiz_id', $quizIds)
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->get()
                ->groupBy('quiz_id');
        }

        // ✅ assignment submissions (grouped by assignment_id)
        $assignmentSubs = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('assignment_submissions') && !empty($assignmentIds)) {
            $assignmentSubs = \App\Models\AssignmentSubmission::query()
                ->where('user_id', $student->id)
                ->whereIn('assignment_id', $assignmentIds)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
                ->groupBy('assignment_id');
        }

        return view('staff.courses.student_progress', compact(
            'course',
            'student',
            'lessonProgress',
            'quizAttempts',
            'assignmentSubs'
        ));
    }
}