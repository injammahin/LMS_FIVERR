<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Division;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search;

        $students = User::where('role', 'student')
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

        return view('admin.students.index', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'division_id' => ['required','exists:divisions,id'],
            'name' => ['required','string','max:255'],
            'password' => ['required','min:6'],
            'login_type' => ['required', Rule::in(['email','username'])],

            'email' => [
                'nullable','email','unique:users,email',
                Rule::requiredIf(fn() => $request->login_type === 'email'),
            ],
            'username' => [
                'nullable','unique:users,username',
                Rule::requiredIf(fn() => $request->login_type === 'username'),
            ],
        ]);

        User::create([
            'division_id' => $request->division_id,
            'name' => $request->name,
            'email' => $request->login_type === 'email' ? $request->email : null,
            'username' => $request->login_type === 'username' ? $request->username : null,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => 'student',
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    public function update(Request $request, User $student)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $student->id,
            'username' => 'nullable|unique:users,username,' . $student->id,
            'password' => 'nullable|min:6',
        ]);

        $student->division_id = $request->division_id; // ✅ add this
        $student->name = $request->name;
        $student->email = $request->email;
        $student->username = $request->username;

        if ($request->password) {
            $student->password = Hash::make($request->password);
            $student->plain_password = $request->password;
        }

        $student->save();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(User $student)
    {
        $student->delete();
        return back()->with('success', 'Student deleted successfully.');
    }
    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        return view('admin.students.create', compact('divisions'));
    }

    public function edit(User $student)
    {
        // abort_if($student->role !== 'student', 404);

        $divisions = Division::orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'divisions'));
    }
    public function toggleStatus(User $student)
    {
        $student->is_active = !$student->is_active;
        $student->save();

        $message = $student->is_active
            ? 'Student reactivated successfully.'
            : 'Student suspended successfully.';

        return back()->with('success', $message);
    }
    public function reports(Request $request)
{
    $rangeDays = (int) ($request->get('range', 30));
    $rangeDays = in_array($rangeDays, [7,30,90]) ? $rangeDays : 30;

    $status = $request->get('status', 'all'); // all|active|suspended
    $search = $request->get('search');
    $perPage = (int) ($request->get('per_page', 10));

    $from = now()->subDays($rangeDays)->startOfDay();

    // Base students query
    $studentsQuery = User::where('role', 'student')
        ->when($status !== 'all', function($q) use ($status) {
            $q->where('is_active', $status === 'active');
        })
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%");
            });
        })
        ->with('division');

    $students = $studentsQuery->latest()->paginate($perPage)->withQueryString();

    // KPIs (global)
    $kpis = [
        'totalStudents' => (int) User::where('role','student')->count(),
        'activeStudents' => (int) User::where('role','student')->where('is_active',1)->count(),
        'suspendedStudents' => (int) User::where('role','student')->where('is_active',0)->count(),

        // these need your tables — adjust if your schema differs:
        'rangeQuizAttempts' => (int) \App\Models\QuizAttempt::where('submitted_at','>=',$from)->count(),
        'rangeAssignmentSubmissions' => (int) \App\Models\AssignmentSubmission::where('created_at','>=',$from)->count(),

        // estimate active in range (students who did quiz/assignment in range)
        'activeInRange' => (int) User::where('role','student')
            ->where(function($q) use ($from){
                $q->whereHas('quizAttempts', fn($qq)=>$qq->where('submitted_at','>=',$from))
                  ->orWhereHas('assignmentSubmissions', fn($qa)=>$qa->where('created_at','>=',$from));
            })->count(),

        // you can compute real progress if you already have progress tables
        'avgOverallProgressAll' => 0,
    ];

    // MAP for current page (fast: only for $students->items())
    $ids = collect($students->items())->pluck('id')->values();

    // Division name map
    $divisionMap = User::whereIn('id', $ids)->with('division:id,name')->get()
        ->mapWithKeys(fn($u)=>[$u->id => ($u->division?->name ?? '—')])->toArray();

    // TODO: Replace these with your real logic:
    // If you already have lesson_progress, quiz_attempts, assignment_submissions,
    // compute totals/submitted/done per student and overall percent.
    $map = [
        'division' => $divisionMap,
        'overall_percent' => [],
        'avg_quiz_grade' => [],
        'lessons_total' => [],
        'lessons_done' => [],
        'quizzes_total' => [],
        'quizzes_submitted' => [],
        'assignments_total' => [],
        'assignments_submitted' => [],
        'last_activity' => [],
    ];

    foreach ($ids as $sid) {
        $map['overall_percent'][$sid] = 0;
        $map['avg_quiz_grade'][$sid] = 0;
        $map['lessons_total'][$sid] = 0;
        $map['lessons_done'][$sid] = 0;
        $map['quizzes_total'][$sid] = 0;
        $map['quizzes_submitted'][$sid] = 0;
        $map['assignments_total'][$sid] = 0;
        $map['assignments_submitted'][$sid] = 0;
        $map['last_activity'][$sid] = null;
    }

    // CHARTS (top 10 by progress)
    $topPairs = collect($ids)->map(fn($sid)=>[
        'id' => $sid,
        'name' => User::find($sid)?->name ?? 'Student',
        'p' => (int)($map['overall_percent'][$sid] ?? 0),
    ])->sortByDesc('p')->take(10)->values();

    $charts = [
        'topLabels' => $topPairs->pluck('name')->toArray(),
        'topProgress' => $topPairs->pluck('p')->toArray(),

        // optional trend placeholders (replace with real daily query)
        'trendLabels' => collect(range(13,0))->map(fn($d)=>now()->subDays($d)->format('d M'))->toArray(),
        'trendQuiz' => array_fill(0, 14, 0),
        'trendAssignments' => array_fill(0, 14, 0),

        // division distribution for current page
        'divLabels' => collect($divisionMap)->values()->unique()->values()->toArray(),
        'divCounts' => collect($divisionMap)->countBy()->values()->toArray(),
    ];

    return view('admin.students.reports', compact('students','rangeDays','status','search','kpis','map','charts'));
}
}