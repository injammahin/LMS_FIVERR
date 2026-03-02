<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $rangeDays = (int)($request->get('range', 30));
        if (!in_array($rangeDays, [7, 30, 90], true)) $rangeDays = 30;

        $now = now();
        $from = $now->copy()->subDays($rangeDays);
        $from14 = $now->copy()->subDays(13);

        // Table checks
        $hasLP = Schema::hasTable('lesson_progress');
        $hasQA = Schema::hasTable('quiz_attempts');
        $hasAS = Schema::hasTable('assignment_submissions');

        // Base counts
        $totalStudents   = User::where('role', 'student')->count();
        $totalTeachers   = User::where('role', 'teacher')->count();
        $totalCourses    = Course::count();
        $totalDivisions  = Division::count();
        $totalSubjects   = Subject::count();

        $suspendedStudents = Schema::hasColumn('users', 'is_active')
            ? User::where('role', 'student')->where('is_active', false)->count()
            : 0;

        $suspendedTeachers = Schema::hasColumn('users', 'is_active')
            ? User::where('role', 'teacher')->where('is_active', false)->count()
            : 0;

        // Content totals
        $totalLessons = Schema::hasTable('lessons') ? DB::table('lessons')->count() : 0;
        $totalQuizzes = Schema::hasTable('quizzes') ? DB::table('quizzes')->count() : 0;
        $totalAssignments = Schema::hasTable('assignments') ? DB::table('assignments')->count() : 0;

        // Students by division
        $studentsByDivision = User::where('role', 'student')
            ->select('division_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('division_id')
            ->pluck('cnt', 'division_id');

        // Courses by division (via subjects)
        $coursesByDivision = collect();
        if (Schema::hasTable('subjects') && Schema::hasTable('courses')) {
            $coursesByDivision = DB::table('courses')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->select('subjects.division_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('subjects.division_id')
                ->pluck('cnt', 'subjects.division_id');
        }

        // Subjects by division
        $subjectsByDivision = Subject::select('division_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('division_id')
            ->pluck('cnt', 'division_id');

        // Active students in last N days
        $activeStudents = 0;
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

        // Activity trends (last 14 days)
        $trendActive = [];
        $trendAssignments = [];
        $trendQuizzes = [];

        for ($i = 0; $i < 14; $i++) {
            $d = $from14->copy()->addDays($i)->toDateString();
            $trendActive[$d] = 0;
            $trendAssignments[$d] = 0;
            $trendQuizzes[$d] = 0;
        }

        // Active trend
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
                if (isset($trendActive[$d])) $trendActive[$d] = (int)$r->cnt;
            }
        }

        // Assignment submissions trend
        if ($hasAS) {
            $rows = DB::table('assignment_submissions')
                ->selectRaw("DATE(created_at) as d, COUNT(*) as cnt")
                ->where('created_at', '>=', $from14)
                ->groupBy('d')
                ->get();

            foreach ($rows as $r) {
                $d = (string)$r->d;
                if (isset($trendAssignments[$d])) $trendAssignments[$d] = (int)$r->cnt;
            }
        }

        // Quiz attempts trend
        if ($hasQA) {
            $rows = DB::table('quiz_attempts')
                ->selectRaw("DATE(COALESCE(submitted_at, updated_at)) as d, COUNT(*) as cnt")
                ->where(function ($w) use ($from14) {
                    $w->where('updated_at', '>=', $from14)
                        ->orWhere('submitted_at', '>=', $from14);
                })
                ->groupBy('d')
                ->get();

            foreach ($rows as $r) {
                $d = (string)$r->d;
                if (isset($trendQuizzes[$d])) $trendQuizzes[$d] = (int)$r->cnt;
            }
        }

        // Recent activity (manual)
        $recentAssignmentSubmissions = collect();
        if ($hasAS) {
            $recentAssignmentSubmissions = DB::table('assignment_submissions')
                ->join('users', 'assignment_submissions.user_id', '=', 'users.id')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->join('courses', 'assignments.course_id', '=', 'courses.id')
                ->select(
                    'assignment_submissions.id',
                    'assignment_submissions.created_at',
                    'users.name as student_name',
                    'users.email',
                    'users.username',
                    'assignments.title as assignment_title',
                    'courses.title as course_title'
                )
                ->orderByDesc('assignment_submissions.created_at')
                ->limit(10)
                ->get();
        }

        $recentQuizAttempts = collect();
        if ($hasQA && Schema::hasTable('quizzes')) {
            $recentQuizAttempts = DB::table('quiz_attempts')
                ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->join('courses', 'quizzes.course_id', '=', 'courses.id')
                ->select(
                    'quiz_attempts.id',
                    'quiz_attempts.updated_at',
                    'quiz_attempts.submitted_at',
                    'quiz_attempts.score',
                    'quiz_attempts.total',
                    'users.name as student_name',
                    'users.email',
                    'users.username',
                    'quizzes.title as quiz_title',
                    'courses.title as course_title'
                )
                ->orderByDesc(DB::raw('COALESCE(quiz_attempts.submitted_at, quiz_attempts.updated_at)'))
                ->limit(10)
                ->get();
        }

        // COURSE ANALYTICS (completion + avg grade)
        $courses = Course::with(['subject.division'])->orderBy('title')->get();
        $courseIds = $courses->pluck('id')->all();

        $lessonsTotalByCourse = Schema::hasTable('lessons')
            ? DB::table('lessons')
                ->select('course_id', DB::raw('COUNT(*) as cnt'))
                ->whereIn('course_id', $courseIds)
                ->groupBy('course_id')
                ->pluck('cnt', 'course_id')
            : collect();

        $quizzesTotalByCourse = Schema::hasTable('quizzes')
            ? DB::table('quizzes')
                ->select('course_id', DB::raw('COUNT(*) as cnt'))
                ->whereIn('course_id', $courseIds)
                ->groupBy('course_id')
                ->pluck('cnt', 'course_id')
            : collect();

        $assignmentsTotalByCourse = Schema::hasTable('assignments')
            ? DB::table('assignments')
                ->select('course_id', DB::raw('COUNT(*) as cnt'))
                ->whereIn('course_id', $courseIds)
                ->groupBy('course_id')
                ->pluck('cnt', 'course_id')
            : collect();

        $doneLessonsByCourse = collect();
        if ($hasLP && Schema::hasTable('lessons') && Schema::hasTable('subjects')) {
            $doneLessonsByCourse = DB::table('lesson_progress')
                ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                ->join('courses', 'lessons.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'lesson_progress.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereNotNull('lesson_progress.completed_at')
                ->select('lessons.course_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('lessons.course_id')
                ->pluck('cnt', 'lessons.course_id');
        }

        $doneQuizzesByCourse = collect();
        $avgGradeByCourse = collect();
        if ($hasQA && Schema::hasTable('quizzes')) {
            $doneQuizzesByCourse = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->join('courses', 'quizzes.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->whereNotNull('quiz_attempts.submitted_at')
                ->select('quizzes.course_id', DB::raw("COUNT(DISTINCT CONCAT(quiz_attempts.user_id,'-',quiz_attempts.quiz_id)) as cnt"))
                ->groupBy('quizzes.course_id')
                ->pluck('cnt', 'quizzes.course_id');

            $avgGradeByCourse = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->whereNotNull('quiz_attempts.submitted_at')
                ->where('quiz_attempts.total', '>', 0)
                ->select('quizzes.course_id', DB::raw('AVG((quiz_attempts.score / quiz_attempts.total) * 100) as avg_pct'))
                ->groupBy('quizzes.course_id')
                ->pluck('avg_pct', 'quizzes.course_id');
        }

        $doneAssignmentsByCourse = collect();
        if ($hasAS && Schema::hasTable('assignments')) {
            $doneAssignmentsByCourse = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->join('courses', 'assignments.course_id', '=', 'courses.id')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->join('users', 'assignment_submissions.user_id', '=', 'users.id')
                ->where('users.role', 'student')
                ->whereColumn('users.division_id', 'subjects.division_id')
                ->select('assignments.course_id', DB::raw("COUNT(DISTINCT CONCAT(assignment_submissions.user_id,'-',assignment_submissions.assignment_id)) as cnt"))
                ->groupBy('assignments.course_id')
                ->pluck('cnt', 'assignments.course_id');
        }

        // Build course insights
        $divisions = Division::orderBy('name')->get();
        $divisionNameMap = $divisions->pluck('name', 'id');
        $courseInsights = collect();
        $overallProgressPercentAllCourses = [];
        $overallGradesAllCourses = [];

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
            if ($avgGrade > 0) $overallGradesAllCourses[] = $avgGrade;

            $courseInsights->push([
                'course' => $course,
                'division' => $divisionNameMap[$divisionId] ?? '—',
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

        $avgOverallGrade = count($overallGradesAllCourses)
            ? (int)round(array_sum($overallGradesAllCourses) / max(1, count($overallGradesAllCourses)))
            : 0;

        // Division progress table (manual + chart)
        $divisionRows = collect();
        foreach ($divisions as $div) {
            $students = (int)($studentsByDivision[$div->id] ?? 0);
            $coursesCount = (int)($coursesByDivision[$div->id] ?? 0);
            $subjectsCount = (int)($subjectsByDivision[$div->id] ?? 0);

            // totals in division (lessons/quizzes/assignments)
            $lt = 0; $qt = 0; $at = 0;

            if (Schema::hasTable('lessons') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
                $lt = (int) DB::table('lessons')
                    ->join('courses', 'lessons.course_id', '=', 'courses.id')
                    ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                    ->where('subjects.division_id', $div->id)
                    ->count();
            }

            if (Schema::hasTable('quizzes') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
                $qt = (int) DB::table('quizzes')
                    ->join('courses', 'quizzes.course_id', '=', 'courses.id')
                    ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                    ->where('subjects.division_id', $div->id)
                    ->count();
            }

            if (Schema::hasTable('assignments') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
                $at = (int) DB::table('assignments')
                    ->join('courses', 'assignments.course_id', '=', 'courses.id')
                    ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                    ->where('subjects.division_id', $div->id)
                    ->count();
            }

            // done in division
            $ld = 0; $qd = 0; $ad = 0;

            if ($hasLP && Schema::hasTable('lessons') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
                $ld = (int) DB::table('lesson_progress')
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('courses', 'lessons.course_id', '=', 'courses.id')
                    ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                    ->join('users', 'lesson_progress.user_id', '=', 'users.id')
                    ->where('users.role', 'student')
                    ->where('subjects.division_id', $div->id)
                    ->whereColumn('users.division_id', 'subjects.division_id')
                    ->whereNotNull('lesson_progress.completed_at')
                    ->count();
            }

            if ($hasQA && Schema::hasTable('quizzes') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
                $qd = (int) DB::table('quiz_attempts')
                    ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                    ->join('courses', 'quizzes.course_id', '=', 'courses.id')
                    ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                    ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
                    ->where('users.role', 'student')
                    ->where('subjects.division_id', $div->id)
                    ->whereColumn('users.division_id', 'subjects.division_id')
                    ->whereNotNull('quiz_attempts.submitted_at')
                    ->distinct(DB::raw("CONCAT(quiz_attempts.user_id,'-',quiz_attempts.quiz_id)"))
                    ->count();
            }

            if ($hasAS && Schema::hasTable('assignments') && Schema::hasTable('courses') && Schema::hasTable('subjects')) {
                $ad = (int) DB::table('assignment_submissions')
                    ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                    ->join('courses', 'assignments.course_id', '=', 'courses.id')
                    ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                    ->join('users', 'assignment_submissions.user_id', '=', 'users.id')
                    ->where('users.role', 'student')
                    ->where('subjects.division_id', $div->id)
                    ->whereColumn('users.division_id', 'subjects.division_id')
                    ->distinct(DB::raw("CONCAT(assignment_submissions.user_id,'-',assignment_submissions.assignment_id)"))
                    ->count();
            }

            $den = $students * ($lt + $qt + $at);
            $num = $ld + $qd + $ad;
            $overallPercent = $den > 0 ? (int)round(($num / $den) * 100) : 0;

            $divisionRows->push([
                'division' => $div,
                'students' => $students,
                'subjects' => $subjectsCount,
                'courses' => $coursesCount,
                'overall_percent' => $overallPercent,
            ]);
        }

        // Teachers table (manual)
        $teachersQuery = User::where('role', 'teacher')
            ->select('id', 'name', 'email', 'username');

        if (Schema::hasColumn('users', 'is_active')) {
            $teachersQuery->addSelect('is_active');
        }

        // if relation exists, add withCount for load
        if (method_exists(User::class, 'coursesTeaching')) {
            $teachersQuery->withCount('coursesTeaching');
        }

        $teachersTable = $teachersQuery
            ->orderBy('name')
            ->limit(12)
            ->get();

        // Activity totals (range)
        $rangeAssignmentSubmissions = $hasAS
            ? (int) DB::table('assignment_submissions')->where('created_at', '>=', $from)->count()
            : 0;

        $rangeQuizAttempts = $hasQA
            ? (int) DB::table('quiz_attempts')->where(function ($w) use ($from) {
                $w->where('updated_at', '>=', $from)
                  ->orWhere('submitted_at', '>=', $from);
            })->count()
            : 0;

        // Charts payload
        $chartActiveLabels = array_keys($trendActive);
        $chartActiveValues = array_values($trendActive);

        $chartAssignmentLabels = array_keys($trendAssignments);
        $chartAssignmentValues = array_values($trendAssignments);

        $chartQuizLabels = array_keys($trendQuizzes);
        $chartQuizValues = array_values($trendQuizzes);

        // Course charts (Top 12 for readability)
        $topCourses = $courseInsights->sortByDesc('students')->take(12)->values();

        $chartCourseLabels = $topCourses->map(fn($r) => $r['course']->title)->values();
        $chartCourseCompletion = $topCourses->map(fn($r) => (int)$r['overall_percent'])->values();
        $chartCourseGrades = $topCourses->map(fn($r) => (int)$r['avg_grade'])->values();

        // Division chart
        $chartDivLabels = $divisionRows->map(fn($r) => $r['division']->name)->values();
        $chartDivProgress = $divisionRows->map(fn($r) => (int)$r['overall_percent'])->values();
        $chartDivStudents = $divisionRows->map(fn($r) => (int)$r['students'])->values();

        return view('admin.analytics.index', [
            'rangeDays' => $rangeDays,

            // KPIs
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'suspendedStudents' => $suspendedStudents,

            'totalTeachers' => $totalTeachers,
            'suspendedTeachers' => $suspendedTeachers,

            'totalCourses' => $totalCourses,
            'totalSubjects' => $totalSubjects,
            'totalDivisions' => $totalDivisions,

            'totalLessons' => $totalLessons,
            'totalQuizzes' => $totalQuizzes,
            'totalAssignments' => $totalAssignments,

            'rangeAssignmentSubmissions' => $rangeAssignmentSubmissions,
            'rangeQuizAttempts' => $rangeQuizAttempts,

            'avgOverallCompletion' => $avgOverallCompletion,
            'avgOverallGrade' => $avgOverallGrade,

            // Tables
            'courseInsights' => $courseInsights,
            'divisionRows' => $divisionRows,
            'teachersTable' => $teachersTable,
            'recentAssignmentSubmissions' => $recentAssignmentSubmissions,
            'recentQuizAttempts' => $recentQuizAttempts,

            // Charts
            'chartActiveLabels' => $chartActiveLabels,
            'chartActiveValues' => $chartActiveValues,

            'chartAssignmentLabels' => $chartAssignmentLabels,
            'chartAssignmentValues' => $chartAssignmentValues,

            'chartQuizLabels' => $chartQuizLabels,
            'chartQuizValues' => $chartQuizValues,

            'chartCourseLabels' => $chartCourseLabels,
            'chartCourseCompletion' => $chartCourseCompletion,
            'chartCourseGrades' => $chartCourseGrades,

            'chartDivLabels' => $chartDivLabels,
            'chartDivProgress' => $chartDivProgress,
            'chartDivStudents' => $chartDivStudents,
        ]);
    }
}