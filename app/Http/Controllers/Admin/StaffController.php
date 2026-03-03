<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $search  = $request->search;

        $staffs = User::where('role', 'staff')
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

        return view('admin.staffs.index', compact('staffs'));
    }

    public function create()
    {
        return view('admin.staffs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|min:6',
            'email' => 'nullable|required_without:username|email|unique:users,email',
            'username' => 'nullable|required_without:email|unique:users,username',
        ]);

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email ?: null,
            'username' => $request->username ?: null,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => 'staff',
            'is_active' => 1,
        ]);

        return redirect()
            ->route('admin.staffs.courses.edit', $staff->id)
            ->with('success', 'Staff created. Now assign courses.');
    }

    public function edit(User $staff)
    {
        abort_if($staff->role !== 'staff', 404);
        return view('admin.staffs.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $staff->id,
            'username' => 'nullable|unique:users,username,' . $staff->id,
            'password' => 'nullable|min:6',
        ]);

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->username = $request->username;

        if ($request->password) {
            $staff->password = Hash::make($request->password);
            $staff->plain_password = $request->password;
        }

        $staff->save();

        return redirect()->route('admin.staffs.index')
            ->with('success', 'Staff updated successfully.');
    }

    public function destroy(User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $staff->coursesSupporting()->detach();
        $staff->delete();

        return back()->with('success', 'Staff deleted successfully.');
    }

    // ✅ Suspend/Activate staff
    public function toggleStatus(User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $staff->is_active = !$staff->is_active;
        $staff->save();

        return back()->with('success', $staff->is_active ? 'Staff activated.' : 'Staff suspended.');
    }

    /**
     * ✅ Assign Courses UI
     * GET: /admin/staffs/{staff}/courses
     */
    public function editCourses(Request $request, User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $divisionId = $request->get('division_id');
        $subjectId  = $request->get('subject_id');
        $search     = $request->get('search');

        $divisions = Division::orderBy('name')->get();

        $subjectsQuery = Subject::with('division')->orderBy('name');
        if ($divisionId) $subjectsQuery->where('division_id', $divisionId);
        $subjects = $subjectsQuery->get();

        $coursesQuery = Course::with(['subject.division'])->orderBy('title');

        if ($subjectId) {
            $coursesQuery->where('subject_id', $subjectId);
        } elseif ($divisionId) {
            $coursesQuery->whereHas('subject', fn($q) => $q->where('division_id', $divisionId));
        }

        if ($search) {
            $coursesQuery->where('title', 'like', "%{$search}%");
        }

        $courses = $coursesQuery->paginate(15)->withQueryString();

        $assigned = $staff->coursesSupporting()->pluck('courses.id')->toArray();

        return view('admin.staffs.courses', compact(
            'staff','courses','assigned',
            'divisions','subjects',
            'divisionId','subjectId','search'
        ));
    }

    /**
     * ✅ Save assigned courses
     * POST: /admin/staffs/{staff}/courses
     */
    public function updateCourses(Request $request, User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $validated = $request->validate([
            'course_ids' => ['nullable','array'],
            'course_ids.*' => ['integer','exists:courses,id'],
        ]);

        $staff->coursesSupporting()->sync($validated['course_ids'] ?? []);

        return back()->with('success', 'Courses assigned successfully.');
    }

    public function reports(Request $request)
    {
        $rangeDays = (int) $request->get('range', 30);
        $rangeDays = in_array($rangeDays, [7, 30, 90], true) ? $rangeDays : 30;

        $status  = $request->get('status', 'all'); // all|active|suspended
        $search  = trim((string) $request->get('search', ''));
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $from = now()->subDays($rangeDays)->startOfDay();

        // staff list (filtered)
        $staffs = User::query()
            ->where('role', 'staff')
            ->when($status === 'active', fn($q) => $q->where('is_active', 1))
            ->when($status === 'suspended', fn($q) => $q->where('is_active', 0))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $staffIds = $staffs->pluck('id')->all();

        // assigned courses per staff
        $courseIdsByStaff = DB::table('course_staff')
            ->whereIn('staff_id', $staffIds)
            ->select('staff_id', 'course_id')
            ->get()
            ->groupBy('staff_id')
            ->map(fn($rows) => $rows->pluck('course_id')->unique()->values());

        $allCourseIds = $courseIdsByStaff->flatten()->unique()->values()->all();

        // course -> division mapping + names
        $courseScope = collect();
        if (!empty($allCourseIds)) {
            $courseScope = DB::table('courses')
                ->join('subjects', 'courses.subject_id', '=', 'subjects.id')
                ->leftJoin('divisions', 'subjects.division_id', '=', 'divisions.id')
                ->whereIn('courses.id', $allCourseIds)
                ->select(
                    'courses.id as course_id',
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'divisions.id as division_id',
                    'divisions.name as division_name'
                )
                ->get()
                ->keyBy('course_id');
        }

        // course content counts
        $courseCounts = collect();
        if (!empty($allCourseIds)) {
            $courseCounts = Course::query()
                ->whereIn('id', $allCourseIds)
                ->withCount(['lessons', 'quizzes', 'assignments'])
                ->get()
                ->keyBy('id');
        }

        // students per division (scope estimate)
        $divisionIds = $courseScope->pluck('division_id')->filter()->unique()->values()->all();
        $studentsPerDivision = collect();
        if (!empty($divisionIds)) {
            $studentsPerDivision = User::query()
                ->where('role', 'student')
                ->whereIn('division_id', $divisionIds)
                ->select('division_id', DB::raw('count(*) as c'))
                ->groupBy('division_id')
                ->pluck('c', 'division_id');
        }

        // activity in staff courses (range)
        $quizAttemptsPerCourse = collect();
        $assignmentSubsPerCourse = collect();
        $lastQuizPerCourse = collect();
        $lastAsgPerCourse = collect();

        if (!empty($allCourseIds)) {
            $quizAttemptsPerCourse = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->whereIn('quizzes.course_id', $allCourseIds)
                ->whereNotNull('quiz_attempts.submitted_at')
                ->where('quiz_attempts.submitted_at', '>=', $from)
                ->select('quizzes.course_id', DB::raw('count(*) as c'))
                ->groupBy('quizzes.course_id')
                ->pluck('c', 'quizzes.course_id');

            $assignmentSubsPerCourse = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->whereIn('assignments.course_id', $allCourseIds)
                ->where('assignment_submissions.created_at', '>=', $from)
                ->select('assignments.course_id', DB::raw('count(*) as c'))
                ->groupBy('assignments.course_id')
                ->pluck('c', 'assignments.course_id');

            $lastQuizPerCourse = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->whereIn('quizzes.course_id', $allCourseIds)
                ->whereNotNull('quiz_attempts.submitted_at')
                ->select('quizzes.course_id', DB::raw('max(quiz_attempts.submitted_at) as last_at'))
                ->groupBy('quizzes.course_id')
                ->pluck('last_at', 'quizzes.course_id');

            $lastAsgPerCourse = DB::table('assignment_submissions')
                ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
                ->whereIn('assignments.course_id', $allCourseIds)
                ->select('assignments.course_id', DB::raw('max(assignment_submissions.created_at) as last_at'))
                ->groupBy('assignments.course_id')
                ->pluck('last_at', 'assignments.course_id');
        }

        // KPIs (global)
        $kpis = [
            'totalStaff' => (int) User::where('role','staff')->count(),
            'activeStaff' => (int) User::where('role','staff')->where('is_active',1)->count(),
            'suspendedStaff' => (int) User::where('role','staff')->where('is_active',0)->count(),
            'assignedCoursesOnPage' => 0,
            'rangeQuizAttemptsOnPage' => (int) $quizAttemptsPerCourse->sum(),
            'rangeAssignmentSubsOnPage' => (int) $assignmentSubsPerCourse->sum(),
        ];

        // per-staff map
        $map = [
            'courses' => [],
            'divisions' => [],
            'lessons' => [],
            'quizzes' => [],
            'assignments' => [],
            'students_scope' => [],
            'range_quiz_attempts' => [],
            'range_assignment_subs' => [],
            'last_activity' => [],
        ];

        foreach ($staffIds as $sid) {
            $cids = collect($courseIdsByStaff[$sid] ?? []);
            $kpis['assignedCoursesOnPage'] += $cids->count();

            // divisions list
            $divNames = $cids->map(function($cid) use ($courseScope) {
                return $courseScope[$cid]->division_name ?? null;
            })->filter()->unique()->values()->take(3);

            $map['divisions'][$sid] = $divNames->implode(', ') ?: '—';

            // content sums
            $less = 0; $quiz = 0; $asg = 0;
            foreach ($cids as $cid) {
                $row = $courseCounts[$cid] ?? null;
                if ($row) {
                    $less += (int) ($row->lessons_count ?? 0);
                    $quiz += (int) ($row->quizzes_count ?? 0);
                    $asg  += (int) ($row->assignments_count ?? 0);
                }
            }

            $map['courses'][$sid] = $cids->count();
            $map['lessons'][$sid] = $less;
            $map['quizzes'][$sid] = $quiz;
            $map['assignments'][$sid] = $asg;

            // students scope (estimate)
            $divisionIdsForStaff = $cids->map(fn($cid) => $courseScope[$cid]->division_id ?? null)->filter()->unique();
            $studentsScope = (int) $divisionIdsForStaff->sum(fn($did) => (int)($studentsPerDivision[$did] ?? 0));
            $map['students_scope'][$sid] = $studentsScope;

            // range activity
            $map['range_quiz_attempts'][$sid] = (int) $cids->sum(fn($cid) => (int)($quizAttemptsPerCourse[$cid] ?? 0));
            $map['range_assignment_subs'][$sid] = (int) $cids->sum(fn($cid) => (int)($assignmentSubsPerCourse[$cid] ?? 0));

            // last activity inside their scope (student submission time)
            $last = null;
            foreach ($cids as $cid) {
                $a = $lastQuizPerCourse[$cid] ?? null;
                $b = $lastAsgPerCourse[$cid] ?? null;
                foreach ([$a, $b] as $dt) {
                    if (!$dt) continue;
                    if (!$last || strtotime($dt) > strtotime($last)) $last = $dt;
                }
            }
            $map['last_activity'][$sid] = $last;
        }

        // charts (top 10)
        $chartRows = $staffs->getCollection()->map(function($s) use ($map) {
            $id = $s->id;
            $activity = (int)($map['range_quiz_attempts'][$id] ?? 0) + (int)($map['range_assignment_subs'][$id] ?? 0);
            return [
                'name' => $s->name,
                'courses' => (int)($map['courses'][$id] ?? 0),
                'activity' => $activity,
            ];
        });

        $topByCourses = $chartRows->sortByDesc('courses')->take(10)->values();
        $topByActivity = $chartRows->sortByDesc('activity')->take(10)->values();

        $charts = [
            'coursesLabels' => $topByCourses->pluck('name'),
            'coursesValues' => $topByCourses->pluck('courses'),
            'activityLabels' => $topByActivity->pluck('name'),
            'activityValues' => $topByActivity->pluck('activity'),
            'statusActive' => (int)($kpis['activeStaff'] ?? 0),
            'statusSuspended' => (int)($kpis['suspendedStaff'] ?? 0),
        ];

        return view('admin.staffs.reports', compact(
            'staffs','rangeDays','status','search','kpis','map','charts'
        ));
    }
}