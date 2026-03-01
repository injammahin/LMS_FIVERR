<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Range for "active" and trend charts
        $rangeDays = (int)($request->get('range', 30));
        if (!in_array($rangeDays, [7, 30, 90], true)) $rangeDays = 30;

        $now = now();
        $from = $now->copy()->subDays($rangeDays);
        $from14 = $now->copy()->subDays(13); // last 14 days (including today)

        // Base counts
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalCourses  = Course::count();
        $totalDivisions = Division::count();

        // Students by division
        $studentsByDivision = User::where('role', 'student')
            ->select('division_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('division_id')
            ->pluck('cnt', 'division_id');

        /**
         * ACTIVE STUDENTS
         * "Active" = did something in last N days:
         * - lesson_progress updated_at
         * - quiz_attempts submitted_at or updated_at
         * - assignment_submissions created_at
         */
        $activeStudents = 0;
        $hasLP = Schema::hasTable('lesson_progress');
        $hasQA = Schema::hasTable('quiz_attempts');
        $hasAS = Schema::hasTable('assignment_submissions');

        if ($hasLP || $hasQA || $hasAS) {
            $q = null;

            if ($hasLP) {
                $q = DB::table('lesson_progress')
                    ->select('user_id')
                    ->where('updated_at', '>=', $from);
            }

            if ($hasQA) {
                $qa = DB::table('quiz_attempts')
                    ->select('user_id')
                    ->where(function ($w) use ($from) {
                        $w->where('updated_at', '>=', $from)
                          ->orWhere('submitted_at', '>=', $from);
                    });

                $q = $q ? $q->union($qa) : $qa;
            }

            if ($hasAS) {
                $as = DB::table('assignment_submissions')
                    ->select('user_id')
                    ->where('created_at', '>=', $from);

                $q = $q ? $q->union($as) : $as;
            }

            $activeStudents = DB::query()
                ->fromSub($q, 'u')
                ->distinct()
                ->count('user_id');
        }

        /**
         * COURSE INSIGHTS (progress + average grades)
         * Progress model:
         * - lessons done = lesson_progress.completed_at not null (per lesson per student)
         * - quizzes done = distinct (user_id, quiz_id) with submitted_at not null
         * - assignments done = distinct (user_id, assignment_id)
         * Denominator = students_in_course_division * (lessons_total + quizzes_total + assignments_total)
         */
        $courses = Course::with(['subject.division'])
            ->orderBy('title')
            ->get();

        $courseIds = $courses->pluck('id')->all();

        // Totals per course
        $lessonsTotalByCourse = Schema::hasTable('lessons')
            ? DB::table('lessons')->select('course_id', DB::raw('COUNT(*) as cnt'))
                ->whereIn('course_id', $courseIds)->groupBy('course_id')->pluck('cnt', 'course_id')
            : collect();

        $quizzesTotalByCourse = Schema::hasTable('quizzes')
            ? DB::table('quizzes')->select('course_id', DB::raw('COUNT(*) as cnt'))
                ->whereIn('course_id', $courseIds)->groupBy('course_id')->pluck('cnt', 'course_id')
            : collect();

        $assignmentsTotalByCourse = Schema::hasTable('assignments')
            ? DB::table('assignments')->select('course_id', DB::raw('COUNT(*) as cnt'))
                ->whereIn('course_id', $courseIds)->groupBy('course_id')->pluck('cnt', 'course_id')
            : collect();

        // Done counts per course (only students matching division of the subject)
        $doneLessonsByCourse = collect();
        if ($hasLP && Schema::hasTable('lessons') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $doneLessonsByCourse = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->join('courses', 'lessons.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'lesson_progress.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereIn('courses.id', $courseIds)
                ->whereNotNull('lesson_progress.completed_at')
                ->select('lessons.course_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('lessons.course_id')
                ->pluck('cnt', 'lessons.course_id');
        }

        $doneQuizzesByCourse = collect();
        $avgGradeByCourse = collect();
        if ($hasQA && Schema::hasTable('quizzes') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            // Done quizzes: distinct user+quiz submitted
            $doneQuizzesByCourse = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->join('courses', 'quizzes.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereIn('courses.id', $courseIds)
                ->whereNotNull('quiz_attempts.submitted_at')
                ->select('quizzes.course_id', DB::raw("COUNT(DISTINCT CONCAT(quiz_attempts.user_id,'-',quiz_attempts.quiz_id)) as cnt"))
                ->groupBy('quizzes.course_id')
                ->pluck('cnt', 'quizzes.course_id');

            // Avg grade per course from attempts that have total > 0
            // (Uses quiz_attempts.score and quiz_attempts.total)
            $avgGradeByCourse = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->whereNotNull('quiz_attempts.submitted_at')
                ->where('quiz_attempts.total', '>', 0)
                ->whereIn('quizzes.course_id', $courseIds)
                ->select('quizzes.course_id', DB::raw('AVG((quiz_attempts.score / quiz_attempts.total) * 100) as avg_pct'))
                ->groupBy('quizzes.course_id')
                ->pluck('avg_pct', 'quizzes.course_id');
        }

        $doneAssignmentsByCourse = collect();
        if ($hasAS && Schema::hasTable('assignments') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $doneAssignmentsByCourse = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->join('courses', 'assignments.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'assignment_submissions.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereIn('courses.id', $courseIds)
                ->select('assignments.course_id', DB::raw("COUNT(DISTINCT CONCAT(assignment_submissions.user_id,'-',assignment_submissions.assignment_id)) as cnt"))
                ->groupBy('assignments.course_id')
                ->pluck('cnt', 'assignments.course_id');
        }

        $courseInsights = collect();
        $overallProgressPercentAllCourses = [];

        foreach ($courses as $course) {
            $divisionId = optional($course->subject)->division_id;

            $studentsInDiv = (int)($studentsByDivision[$divisionId] ?? 0);

            $lt = (int)($lessonsTotalByCourse[$course->id] ?? 0);
            $qt = (int)($quizzesTotalByCourse[$course->id] ?? 0);
            $at = (int)($assignmentsTotalByCourse[$course->id] ?? 0);

            $ld = (int)($doneLessonsByCourse[$course->id] ?? 0);
            $qd = (int)($doneQuizzesByCourse[$course->id] ?? 0);
            $ad = (int)($doneAssignmentsByCourse[$course->id] ?? 0);

            $den = $studentsInDiv * ($lt + $qt + $at);
            $num = $ld + $qd + $ad;

            $overallPercent = $den > 0 ? (int)round(($num / $den) * 100) : 0;

            $avgGrade = (float)($avgGradeByCourse[$course->id] ?? 0);
            $avgGrade = is_nan($avgGrade) ? 0 : $avgGrade;
            $avgGrade = (int)round($avgGrade);

            $overallProgressPercentAllCourses[] = $overallPercent;

            $courseInsights->push([
                'course' => $course,
                'students' => $studentsInDiv,
                'lessons_total' => $lt,
                'quizzes_total' => $qt,
                'assignments_total' => $at,
                'lesson_done' => $ld,
                'quiz_done' => $qd,
                'assignment_done' => $ad,
                'overall_percent' => $overallPercent,
                'avg_grade' => $avgGrade,
            ]);
        }

        $avgOverallCompletion = count($overallProgressPercentAllCourses)
            ? (int)round(array_sum($overallProgressPercentAllCourses) / max(1, count($overallProgressPercentAllCourses)))
            : 0;

        /**
         * DIVISION PROGRESS (overall)
         */
        $divisions = Division::orderBy('name')->get();
        $divisionRows = collect();

        // Totals per division
        $divLessonsTotal = collect();
        $divQuizzesTotal = collect();
        $divAssignmentsTotal = collect();

        if (Schema::hasTable('lessons') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $divLessonsTotal = DB::table('lessons')
                ->join('courses', 'lessons.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->select('subjects.division_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('subjects.division_id')
                ->pluck('cnt', 'subjects.division_id');
        }

        if (Schema::hasTable('quizzes') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $divQuizzesTotal = DB::table('quizzes')
                ->join('courses', 'quizzes.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->select('subjects.division_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('subjects.division_id')
                ->pluck('cnt', 'subjects.division_id');
        }

        if (Schema::hasTable('assignments') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $divAssignmentsTotal = DB::table('assignments')
                ->join('courses', 'assignments.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->select('subjects.division_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('subjects.division_id')
                ->pluck('cnt', 'subjects.division_id');
        }

        // Done per division
        $divLessonsDone = collect();
        if ($hasLP && Schema::hasTable('lessons') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $divLessonsDone = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->join('courses', 'lessons.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'lesson_progress.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereNotNull('lesson_progress.completed_at')
                ->select('subjects.division_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('subjects.division_id')
                ->pluck('cnt', 'subjects.division_id');
        }

        $divQuizzesDone = collect();
        if ($hasQA && Schema::hasTable('quizzes') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $divQuizzesDone = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->join('courses', 'quizzes.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereNotNull('quiz_attempts.submitted_at')
                ->select('subjects.division_id', DB::raw("COUNT(DISTINCT CONCAT(quiz_attempts.user_id,'-',quiz_attempts.quiz_id)) as cnt"))
                ->groupBy('subjects.division_id')
                ->pluck('cnt', 'subjects.division_id');
        }

        $divAssignmentsDone = collect();
        if ($hasAS && Schema::hasTable('assignments') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $divAssignmentsDone = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->join('courses', 'assignments.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'assignment_submissions.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->select('subjects.division_id', DB::raw("COUNT(DISTINCT CONCAT(assignment_submissions.user_id,'-',assignment_submissions.assignment_id)) as cnt"))
                ->groupBy('subjects.division_id')
                ->pluck('cnt', 'subjects.division_id');
        }

        foreach ($divisions as $div) {
            $students = (int)($studentsByDivision[$div->id] ?? 0);

            $lt = (int)($divLessonsTotal[$div->id] ?? 0);
            $qt = (int)($divQuizzesTotal[$div->id] ?? 0);
            $at = (int)($divAssignmentsTotal[$div->id] ?? 0);

            $ld = (int)($divLessonsDone[$div->id] ?? 0);
            $qd = (int)($divQuizzesDone[$div->id] ?? 0);
            $ad = (int)($divAssignmentsDone[$div->id] ?? 0);

            $den = $students * ($lt + $qt + $at);
            $num = $ld + $qd + $ad;

            $overallPercent = $den > 0 ? (int)round(($num / $den) * 100) : 0;

            $divisionRows->push([
                'division' => $div,
                'students' => $students,
                'lessons_total' => $lt,
                'quizzes_total' => $qt,
                'assignments_total' => $at,
                'overall_percent' => $overallPercent,
            ]);
        }

        /**
         * AT-RISK STUDENTS (simple + fully dynamic)
         * - progress percent low OR avg quiz < threshold OR inactive.
         * progress percent is computed vs totals in their division.
         */
        $riskProgressThreshold = 30;
        $riskGradeThreshold = 40;
        $riskInactiveDays = 14;
        $inactiveBefore = $now->copy()->subDays($riskInactiveDays);

        $students = User::where('role', 'student')
            ->select('id', 'name', 'username', 'email', 'division_id')
            ->orderBy('name')
            ->get();

        // division totals (items) for per-student progress
        $divisionItemTotals = [];
        foreach ($divisions as $div) {
            $divisionItemTotals[$div->id] = (int)($divLessonsTotal[$div->id] ?? 0)
                + (int)($divQuizzesTotal[$div->id] ?? 0)
                + (int)($divAssignmentsTotal[$div->id] ?? 0);
        }

        // done lessons per student (division matched)
        $doneLessonsByStudent = collect();
        if ($hasLP && Schema::hasTable('lessons') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $doneLessonsByStudent = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->join('courses', 'lessons.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'lesson_progress.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereNotNull('lesson_progress.completed_at')
                ->select('lesson_progress.user_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('lesson_progress.user_id')
                ->pluck('cnt', 'lesson_progress.user_id');
        }

        // done quizzes per student (distinct quiz)
        $doneQuizzesByStudent = collect();
        $avgQuizByStudent = collect();
        if ($hasQA && Schema::hasTable('quizzes') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $doneQuizzesByStudent = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->join('courses', 'quizzes.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereNotNull('quiz_attempts.submitted_at')
                ->select('quiz_attempts.user_id', DB::raw('COUNT(DISTINCT quiz_attempts.quiz_id) as cnt'))
                ->groupBy('quiz_attempts.user_id')
                ->pluck('cnt', 'quiz_attempts.user_id');

            $avgQuizByStudent = DB::table('quiz_attempts')
                ->whereNotNull('quiz_attempts.submitted_at')
                ->where('quiz_attempts.total', '>', 0)
                ->select('quiz_attempts.user_id', DB::raw('AVG((quiz_attempts.score / quiz_attempts.total) * 100) as avg_pct'))
                ->groupBy('quiz_attempts.user_id')
                ->pluck('avg_pct', 'quiz_attempts.user_id');
        }

        // done assignments per student (distinct assignment)
        $doneAssignmentsByStudent = collect();
        if ($hasAS && Schema::hasTable('assignments') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
            $doneAssignmentsByStudent = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->join('courses', 'assignments.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'assignment_submissions.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->select('assignment_submissions.user_id', DB::raw('COUNT(DISTINCT assignment_submissions.assignment_id) as cnt'))
                ->groupBy('assignment_submissions.user_id')
                ->pluck('cnt', 'assignment_submissions.user_id');
        }

        // last activity by student (max of recent timestamps)
        $lastActivity = []; // user_id => timestamp
        if ($hasLP) {
            $rows = DB::table('lesson_progress')
                ->select('user_id', DB::raw('MAX(updated_at) as mx'))
                ->groupBy('user_id')
                ->get();
            foreach ($rows as $r) $lastActivity[(int)$r->user_id] = max($lastActivity[(int)$r->user_id] ?? '1970-01-01', $r->mx);
        }
        if ($hasQA) {
            $rows = DB::table('quiz_attempts')
                ->select('user_id', DB::raw('MAX(COALESCE(submitted_at, updated_at)) as mx'))
                ->groupBy('user_id')
                ->get();
            foreach ($rows as $r) $lastActivity[(int)$r->user_id] = max($lastActivity[(int)$r->user_id] ?? '1970-01-01', $r->mx);
        }
        if ($hasAS) {
            $rows = DB::table('assignment_submissions')
                ->select('user_id', DB::raw('MAX(created_at) as mx'))
                ->groupBy('user_id')
                ->get();
            foreach ($rows as $r) $lastActivity[(int)$r->user_id] = max($lastActivity[(int)$r->user_id] ?? '1970-01-01', $r->mx);
        }

        $divisionNameMap = $divisions->pluck('name', 'id');

        $atRiskRows = collect();
        foreach ($students as $st) {
            $divId = (int)($st->division_id ?? 0);
            $totalItems = (int)($divisionItemTotals[$divId] ?? 0);

            $done = (int)($doneLessonsByStudent[$st->id] ?? 0)
                + (int)($doneQuizzesByStudent[$st->id] ?? 0)
                + (int)($doneAssignmentsByStudent[$st->id] ?? 0);

            $progress = $totalItems > 0 ? (int)round(($done / $totalItems) * 100) : 0;

            $avgQuiz = (float)($avgQuizByStudent[$st->id] ?? 0);
            $avgQuiz = is_nan($avgQuiz) ? 0 : $avgQuiz;
            $avgQuiz = (int)round($avgQuiz);

            $last = $lastActivity[$st->id] ?? null;
            $lastCarbon = $last ? \Illuminate\Support\Carbon::parse($last) : null;
            $inactive = $lastCarbon ? $lastCarbon->lt($inactiveBefore) : true;

            $isRisk = ($progress < $riskProgressThreshold) || ($avgQuiz > 0 && $avgQuiz < $riskGradeThreshold) || $inactive;

            if ($isRisk) {
                $atRiskRows->push([
                    'student' => $st,
                    'division' => $divisionNameMap[$divId] ?? '—',
                    'progress' => $progress,
                    'avg_quiz' => $avgQuiz,
                    'last_active' => $lastCarbon,
                    'inactive' => $inactive,
                ]);
            }
        }

        // sort riskiest first
        $atRiskRows = $atRiskRows
            ->sortBy([
                fn($r) => $r['progress'],
                fn($r) => $r['avg_quiz'] ?: 999,
            ])
            ->take(12)
            ->values();

        $atRiskCount = $atRiskRows->count();

        /**
         * ACTIVE TREND (last 14 days) — count distinct active students per day
         */
        $trend = [];
        for ($i = 0; $i < 14; $i++) {
            $d = $from14->copy()->addDays($i)->toDateString();
            $trend[$d] = 0;
        }

        if ($hasLP || $hasQA || $hasAS) {
            $parts = [];

            if ($hasLP) {
                $parts[] = DB::table('lesson_progress')
                    ->selectRaw("DATE(updated_at) as d, user_id")
                    ->where('updated_at', '>=', $from14);
            }
            if ($hasQA) {
                $parts[] = DB::table('quiz_attempts')
                    ->selectRaw("DATE(COALESCE(submitted_at, updated_at)) as d, user_id")
                    ->where(function ($w) use ($from14) {
                        $w->where('updated_at', '>=', $from14)
                          ->orWhere('submitted_at', '>=', $from14);
                    });
            }
            if ($hasAS) {
                $parts[] = DB::table('assignment_submissions')
                    ->selectRaw("DATE(created_at) as d, user_id")
                    ->where('created_at', '>=', $from14);
            }

            $union = array_shift($parts);
            foreach ($parts as $p) $union->unionAll($p);

            $rows = DB::query()
                ->fromSub($union, 't')
                ->select('d', DB::raw('COUNT(DISTINCT user_id) as cnt'))
                ->groupBy('d')
                ->get();

            foreach ($rows as $r) {
                $d = (string)$r->d;
                if (isset($trend[$d])) $trend[$d] = (int)$r->cnt;
            }
        }

        // Chart payloads
        $chartActiveLabels = array_keys($trend);
        $chartActiveValues = array_values($trend);

        $chartCourseLabels = $courseInsights->map(fn($r) => $r['course']->title)->values();
        $chartCourseGrades = $courseInsights->map(fn($r) => (int)$r['avg_grade'])->values();

        $chartDivLabels = $divisionRows->map(fn($r) => $r['division']->name)->values();
        $chartDivProgress = $divisionRows->map(fn($r) => (int)$r['overall_percent'])->values();
        $chartDivStudents = $divisionRows->map(fn($r) => (int)$r['students'])->values();

        return view('admin.dashboard', [
            'rangeDays' => $rangeDays,

            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'atRiskCount' => $atRiskCount,

            'totalCourses' => $totalCourses,
            'totalTeachers' => $totalTeachers,
            'totalDivisions' => $totalDivisions,

            'avgOverallCompletion' => $avgOverallCompletion,

            'courseInsights' => $courseInsights,
            'divisionRows' => $divisionRows,
            'atRiskRows' => $atRiskRows,

            'chartActiveLabels' => $chartActiveLabels,
            'chartActiveValues' => $chartActiveValues,
            'chartCourseLabels' => $chartCourseLabels,
            'chartCourseGrades' => $chartCourseGrades,
            'chartDivLabels' => $chartDivLabels,
            'chartDivProgress' => $chartDivProgress,
            'chartDivStudents' => $chartDivStudents,
        ]);
    }
}