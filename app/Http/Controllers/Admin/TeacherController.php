<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search;

        $teachers = User::where('role', 'teacher')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('username', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:6'],
            'login_type' => ['required', Rule::in(['email', 'username'])],

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email'),
                Rule::requiredIf(fn () => $request->login_type === 'email'),
            ],
            'username' => [
                'nullable',
                Rule::unique('users', 'username'),
                Rule::requiredIf(fn () => $request->login_type === 'username'),
            ],
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->login_type === 'email' ? $request->email : null,
            'username' => $request->login_type === 'username' ? $request->username : null,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password, // ⚠️ not recommended in production
            'role' => 'teacher',
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.teachers.courses.edit', $teacher->id)
            ->with('success', 'Teacher created successfully. Now assign courses.');
    }

    public function edit(User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'password'   => ['nullable', 'string', 'min:6'],
            'login_type' => ['required', Rule::in(['email', 'username'])],

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($teacher->id),
                Rule::requiredIf(fn () => $request->login_type === 'email'),
            ],
            'username' => [
                'nullable',
                Rule::unique('users', 'username')->ignore($teacher->id),
                Rule::requiredIf(fn () => $request->login_type === 'username'),
            ],
        ]);

        $teacher->name = $request->name;

        // ✅ Only one login method is stored
        $teacher->email = $request->login_type === 'email' ? $request->email : null;
        $teacher->username = $request->login_type === 'username' ? $request->username : null;

        if ($request->filled('password')) {
            $teacher->password = Hash::make($request->password);
            $teacher->plain_password = $request->password; // ⚠️ not recommended in production
        }

        $teacher->save();

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        // detach relation if exists
        if (method_exists($teacher, 'coursesTeaching')) {
            $teacher->coursesTeaching()->detach();
        }

        $teacher->delete();

        return back()->with('success', 'Teacher deleted successfully.');
    }

    /**
     * ✅ Admin suspend/reactivate teacher
     */
    public function toggleStatus(User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        $teacher->is_active = !$teacher->is_active;
        $teacher->save();

        return back()->with(
            'success',
            $teacher->is_active ? 'Teacher activated successfully.' : 'Teacher suspended successfully.'
        );
    }

    /**
     * ✅ Assign Courses UI
     * GET: /admin/teachers/{teacher}/courses
     */
    public function editCourses(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        $divisionId = $request->get('division_id');
        $subjectId  = $request->get('subject_id');
        $search     = $request->get('search');

        $divisions = Division::orderBy('name')->get();

        $subjectsQuery = Subject::with('division')->orderBy('name');
        if ($divisionId) {
            $subjectsQuery->where('division_id', $divisionId);
        }
        $subjects = $subjectsQuery->get();

        $coursesQuery = Course::with(['subject.division'])->orderBy('title');

        if ($subjectId) {
            $coursesQuery->where('subject_id', $subjectId);
        } elseif ($divisionId) {
            $coursesQuery->whereHas('subject', fn ($q) => $q->where('division_id', $divisionId));
        }

        if ($search) {
            $coursesQuery->where('title', 'like', "%{$search}%");
        }

        $courses = $coursesQuery->paginate(15)->withQueryString();

        $assigned = method_exists($teacher, 'coursesTeaching')
            ? $teacher->coursesTeaching()->pluck('courses.id')->toArray()
            : [];

        return view('admin.teachers.courses', compact(
            'teacher',
            'courses',
            'assigned',
            'divisions',
            'subjects',
            'divisionId',
            'subjectId',
            'search'
        ));
    }

    /**
     * ✅ Save assigned courses
     * POST: /admin/teachers/{teacher}/courses
     */
    public function updateCourses(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        $validated = $request->validate([
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);

        $courseIds = $validated['course_ids'] ?? [];

        if (!method_exists($teacher, 'coursesTeaching')) {
            return back()->with('error', 'coursesTeaching() relation not found on User model.');
        }

        $teacher->coursesTeaching()->sync($courseIds);

        return redirect()
            ->route('admin.teachers.courses.edit', $teacher->id)
            ->with('success', 'Courses assigned successfully.');
    }
    public function show(User $teacher)
    {
        // If someone visits /admin/teachers/{id}
        return redirect()->route('admin.teachers.edit', $teacher->id);
    }


    public function reports(Request $request)
    {
        $perPage = (int)($request->get('per_page', 10));
        if (!in_array($perPage, [10, 25, 50], true)) $perPage = 10;

        $rangeDays = (int)($request->get('range', 30));
        if (!in_array($rangeDays, [7, 30, 90], true)) $rangeDays = 30;

        $status = $request->get('status', 'all'); // all|active|suspended
        $search = $request->get('search');

        $from = now()->subDays($rangeDays);

        $teachers = User::query()
            ->where('role', 'teacher')
            ->when($status === 'active', fn($q) => $q->where('is_active', 1))
            ->when($status === 'suspended', fn($q) => $q->where('is_active', 0))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $teacherIds = $teachers->pluck('id')->all();

        // If no rows, return empty safe payloads
        if (!$teacherIds) {
            return view('admin.teachers.reports', [
                'teachers' => $teachers,
                'rangeDays' => $rangeDays,
                'status' => $status,
                'search' => $search,

                'kpis' => [
                    'totalTeachers' => 0,
                    'activeTeachers' => 0,
                    'suspendedTeachers' => 0,
                    'assignedCourses' => 0,
                    'pendingAssignments' => 0,
                    'pendingQuizzes' => 0,
                    'avgGradeAll' => 0,
                ],

                'map' => [
                    'courses' => [],
                    'lessons' => [],
                    'quizzes' => [],
                    'assignments' => [],
                    'pending_assignments' => [],
                    'pending_quizzes' => [],
                    'graded_assignments' => [],
                    'graded_quizzes' => [],
                    'avg_grade' => [],
                    'last_activity' => [],
                ],

                'charts' => [
                    'topLabels' => [],
                    'topCourses' => [],
                    'topPending' => [],
                    'topGrades' => [],
                ],
            ]);
        }

        /**
         * IMPORTANT:
         * Your pivot table for teacher-course is assumed as: course_teacher
         * columns: teacher_id, course_id
         * (If yours is different, tell me the name and I’ll adjust)
         */

        // Courses count per teacher
        $coursesByTeacher = DB::table('course_teacher')
            ->select('teacher_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('teacher_id', $teacherIds)
            ->groupBy('teacher_id')
            ->pluck('cnt', 'teacher_id');

        // Lessons count per teacher
        $lessonsByTeacher = DB::table('course_teacher')
            ->join('lessons', 'lessons.course_id', '=', 'course_teacher.course_id')
            ->select('course_teacher.teacher_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->groupBy('course_teacher.teacher_id')
            ->pluck('cnt', 'course_teacher.teacher_id');

        // Quizzes count per teacher
        $quizzesByTeacher = DB::table('course_teacher')
            ->join('quizzes', 'quizzes.course_id', '=', 'course_teacher.course_id')
            ->select('course_teacher.teacher_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->groupBy('course_teacher.teacher_id')
            ->pluck('cnt', 'course_teacher.teacher_id');

        // Assignments count per teacher
        $assignmentsByTeacher = DB::table('course_teacher')
            ->join('assignments', 'assignments.course_id', '=', 'course_teacher.course_id')
            ->select('course_teacher.teacher_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->groupBy('course_teacher.teacher_id')
            ->pluck('cnt', 'course_teacher.teacher_id');

        // Pending assignment grading (status = submitted)
        $pendingAssignmentsByTeacher = DB::table('course_teacher')
            ->join('assignments', 'assignments.course_id', '=', 'course_teacher.course_id')
            ->join('assignment_submissions', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->where('assignment_submissions.status', 'submitted')
            ->select('course_teacher.teacher_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('course_teacher.teacher_id')
            ->pluck('cnt', 'course_teacher.teacher_id');

        // Graded assignment submissions (status = graded)
        $gradedAssignmentsByTeacher = DB::table('course_teacher')
            ->join('assignments', 'assignments.course_id', '=', 'course_teacher.course_id')
            ->join('assignment_submissions', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->where('assignment_submissions.status', 'graded')
            ->select('course_teacher.teacher_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('course_teacher.teacher_id')
            ->pluck('cnt', 'course_teacher.teacher_id');

        // Pending quiz grading (status in submitted, reviewed)
        $pendingQuizzesByTeacher = DB::table('course_teacher')
            ->join('quizzes', 'quizzes.course_id', '=', 'course_teacher.course_id')
            ->join('quiz_attempts', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->whereIn('quiz_attempts.status', ['submitted', 'reviewed'])
            ->select('course_teacher.teacher_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('course_teacher.teacher_id')
            ->pluck('cnt', 'course_teacher.teacher_id');

        // Graded quiz attempts (status = graded)
        $gradedQuizzesByTeacher = DB::table('course_teacher')
            ->join('quizzes', 'quizzes.course_id', '=', 'course_teacher.course_id')
            ->join('quiz_attempts', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->where('quiz_attempts.status', 'graded')
            ->select('course_teacher.teacher_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('course_teacher.teacher_id')
            ->pluck('cnt', 'course_teacher.teacher_id');

        // Avg quiz grade per teacher (range filtered)
        $avgGradeByTeacher = DB::table('course_teacher')
            ->join('quizzes', 'quizzes.course_id', '=', 'course_teacher.course_id')
            ->join('quiz_attempts', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->whereNotNull('quiz_attempts.submitted_at')
            ->where('quiz_attempts.submitted_at', '>=', $from)
            ->where('quiz_attempts.total', '>', 0)
            ->select('course_teacher.teacher_id', DB::raw('AVG((quiz_attempts.score/quiz_attempts.total)*100) as avg_pct'))
            ->groupBy('course_teacher.teacher_id')
            ->pluck('avg_pct', 'course_teacher.teacher_id');

        // Last activity per teacher (max of quiz submitted / assignment submitted in range)
        $lastQuizActivity = DB::table('course_teacher')
            ->join('quizzes', 'quizzes.course_id', '=', 'course_teacher.course_id')
            ->join('quiz_attempts', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->whereNotNull('quiz_attempts.submitted_at')
            ->select('course_teacher.teacher_id', DB::raw('MAX(quiz_attempts.submitted_at) as mx'))
            ->groupBy('course_teacher.teacher_id')
            ->pluck('mx', 'course_teacher.teacher_id');

        $lastAssActivity = DB::table('course_teacher')
            ->join('assignments', 'assignments.course_id', '=', 'course_teacher.course_id')
            ->join('assignment_submissions', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->whereIn('course_teacher.teacher_id', $teacherIds)
            ->select('course_teacher.teacher_id', DB::raw('MAX(assignment_submissions.created_at) as mx'))
            ->groupBy('course_teacher.teacher_id')
            ->pluck('mx', 'course_teacher.teacher_id');

        $lastActivity = [];
        foreach ($teacherIds as $tid) {
            $a = $lastQuizActivity[$tid] ?? null;
            $b = $lastAssActivity[$tid] ?? null;
            $lastActivity[$tid] = max($a ?? '1970-01-01', $b ?? '1970-01-01');
            if ($lastActivity[$tid] === '1970-01-01') $lastActivity[$tid] = null;
        }

        // KPIs (global, not paginated)
        $totalTeachers = User::where('role', 'teacher')->count();
        $activeTeachers = User::where('role', 'teacher')->where('is_active', 1)->count();
        $suspendedTeachers = User::where('role', 'teacher')->where('is_active', 0)->count();

        // Based on current page (fast)
        $assignedCourses = (int)collect($coursesByTeacher)->sum();

        $pendingAssignments = (int)collect($pendingAssignmentsByTeacher)->sum();
        $pendingQuizzes = (int)collect($pendingQuizzesByTeacher)->sum();

        $avgGradeAll = (int)round(collect($avgGradeByTeacher)->avg() ?? 0);

        // Top 10 chart data (based on current page to keep it fast)
        $top = collect($teacherIds)->map(function ($tid) use ($teachers, $coursesByTeacher, $pendingAssignmentsByTeacher, $pendingQuizzesByTeacher, $avgGradeByTeacher) {
            $t = $teachers->firstWhere('id', $tid);
            $pending = (int)($pendingAssignmentsByTeacher[$tid] ?? 0) + (int)($pendingQuizzesByTeacher[$tid] ?? 0);
            return [
                'name' => $t?->name ?? 'Teacher',
                'courses' => (int)($coursesByTeacher[$tid] ?? 0),
                'pending' => $pending,
                'avg' => (int)round((float)($avgGradeByTeacher[$tid] ?? 0)),
            ];
        })->sortByDesc('courses')->take(10)->values();

        return view('admin.teachers.reports', [
            'teachers' => $teachers,
            'rangeDays' => $rangeDays,
            'status' => $status,
            'search' => $search,

            'kpis' => [
                'totalTeachers' => $totalTeachers,
                'activeTeachers' => $activeTeachers,
                'suspendedTeachers' => $suspendedTeachers,
                'assignedCourses' => $assignedCourses,
                'pendingAssignments' => $pendingAssignments,
                'pendingQuizzes' => $pendingQuizzes,
                'avgGradeAll' => $avgGradeAll,
            ],

            'map' => [
                'courses' => $coursesByTeacher,
                'lessons' => $lessonsByTeacher,
                'quizzes' => $quizzesByTeacher,
                'assignments' => $assignmentsByTeacher,
                'pending_assignments' => $pendingAssignmentsByTeacher,
                'pending_quizzes' => $pendingQuizzesByTeacher,
                'graded_assignments' => $gradedAssignmentsByTeacher,
                'graded_quizzes' => $gradedQuizzesByTeacher,
                'avg_grade' => $avgGradeByTeacher,
                'last_activity' => $lastActivity,
            ],

            'charts' => [
                'topLabels' => $top->pluck('name')->values(),
                'topCourses' => $top->pluck('courses')->values(),
                'topPending' => $top->pluck('pending')->values(),
                'topGrades' => $top->pluck('avg')->values(),
            ],
        ]);
    }
}