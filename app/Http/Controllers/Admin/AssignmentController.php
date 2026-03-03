<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\AssignmentSubmission;
use App\Models\Division;
use App\Models\Subject;
class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        $assignments = $course->assignments()->latest()->paginate(15);
        return view('admin.assignments.index', compact('course','assignments'));
    }

    public function create(Course $course)
    {
        return view('admin.assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'attachment' => ['nullable','file','max:51200','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip'],

            'submission_type' => ['required','in:text,file,text_file'],
            'grading_type' => ['required','in:points,pass_fail'],
            'total_marks' => ['nullable','integer','min:1','max:10000'],
            'max_attempts' => ['nullable','integer','min:1','max:100'],

            'due_at' => ['nullable','date'],
            'allow_late' => ['nullable','boolean'],
            'late_until' => ['nullable','date'],

            'status' => ['required','in:draft,published'],
        ]);

        // if points grading, total_marks recommended
        if (($validated['grading_type'] ?? null) === 'points' && empty($validated['total_marks'])) {
            return back()->withErrors(['total_marks' => 'Total marks is required for points grading.'])->withInput();
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('assignments', 'public');
        }

        $allowLate = $request->boolean('allow_late');

        if (!$allowLate) {
            $validated['late_until'] = null;
        }

        Assignment::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'attachment' => $attachmentPath,

            'submission_type' => $validated['submission_type'],
            'grading_type' => $validated['grading_type'],
            'total_marks' => $validated['total_marks'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? null,

            'due_at' => $validated['due_at'] ?? null,
            'allow_late' => $allowLate,
            'late_until' => $validated['late_until'] ?? null,

            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.courses.assignments.index', $course->id)
            ->with('success', 'Assignment created successfully.');
    }

    public function edit(Course $course, Assignment $assignment)
    {
        // abort_if($assignment->course_id !== $course->id, 404);
        return view('admin.assignments.edit', compact('course','assignment'));
    }

    public function update(Request $request, Course $course, Assignment $assignment)
    {
        // abort_if($assignment->course_id !== $course->id, 404);

        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],

            'attachment' => ['nullable','file','max:51200','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip'],
            'remove_attachment' => ['nullable','boolean'],

            'submission_type' => ['required','in:text,file,text_file'],
            'grading_type' => ['required','in:points,pass_fail'],
            'total_marks' => ['nullable','integer','min:1','max:10000'],
            'max_attempts' => ['nullable','integer','min:1','max:100'],

            'due_at' => ['nullable','date'],
            'allow_late' => ['nullable','boolean'],
            'late_until' => ['nullable','date'],

            'status' => ['required','in:draft,published'],
        ]);

        if (($validated['grading_type'] ?? null) === 'points' && empty($validated['total_marks'])) {
            return back()->withErrors(['total_marks' => 'Total marks is required for points grading.'])->withInput();
        }

        if ($request->boolean('remove_attachment') && $assignment->attachment) {
            Storage::disk('public')->delete($assignment->attachment);
            $assignment->attachment = null;
        }

        if ($request->hasFile('attachment')) {
            if ($assignment->attachment) Storage::disk('public')->delete($assignment->attachment);
            $assignment->attachment = $request->file('attachment')->store('assignments', 'public');
        }

        $allowLate = $request->boolean('allow_late');
        if (!$allowLate) $validated['late_until'] = null;

        $assignment->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'attachment' => $assignment->attachment,

            'submission_type' => $validated['submission_type'],
            'grading_type' => $validated['grading_type'],
            'total_marks' => $validated['total_marks'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? null,

            'due_at' => $validated['due_at'] ?? null,
            'allow_late' => $allowLate,
            'late_until' => $validated['late_until'] ?? null,

            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.courses.assignments.index', $course->id)
            ->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Course $course, Assignment $assignment)
    {
        // abort_if($assignment->course_id !== $course->id, 404);

        if ($assignment->attachment) Storage::disk('public')->delete($assignment->attachment);

        $assignment->delete();

        return redirect()
            ->route('admin.courses.assignments.index', $course->id)
            ->with('success', 'Assignment deleted successfully.');
    }
     public function graded(Request $request)
    {
        $rangeDays = (int) $request->get('range', 30);
        $rangeDays = in_array($rangeDays, [7, 30, 90], true) ? $rangeDays : 30;

        $divisionId = $request->get('division_id');
        $subjectId  = $request->get('subject_id');
        $courseId   = $request->get('course_id');
        $status = 'graded';
        $type = $request->get('type', 'all'); // all|points|pass_fail
        $search  = trim((string) $request->get('search', ''));
        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 15;

        $from = now()->subDays($rangeDays)->startOfDay();

        $hasGradedAt = Schema::hasColumn('assignment_submissions', 'graded_at');
        $gradedAtCol = $hasGradedAt ? 'graded_at' : 'updated_at';

        // ---------- Filters lists ----------
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
        $coursesList = $coursesQuery->get();

        // ---------- Base query (graded only) ----------
        $subTable = (new AssignmentSubmission)->getTable(); // "assignment_submissions"

        $base = AssignmentSubmission::query()
            ->with(['user', 'assignment.course.subject.division'])
            ->where("$subTable.status", 'graded')                     // ✅ FIX
            ->where("$subTable.$gradedAtCol", '>=', $from);

        if ($courseId) {
            $base->whereHas('assignment', fn($q) => $q->where('course_id', $courseId));
        } elseif ($subjectId) {
            $base->whereHas('assignment.course', fn($q) => $q->where('subject_id', $subjectId));
        } elseif ($divisionId) {
            $base->whereHas('assignment.course.subject', fn($q) => $q->where('division_id', $divisionId));
        }

        if ($type === 'points') {
            $base->whereHas('assignment', fn($q) => $q->where('grading_type', 'points'));
        } elseif ($type === 'pass_fail') {
            $base->whereHas('assignment', fn($q) => $q->where('grading_type', 'pass_fail'));
        }

        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                })->orWhereHas('assignment', function ($a) use ($search) {
                    $a->where('title', 'like', "%{$search}%");
                })->orWhereHas('assignment.course', function ($c) use ($search) {
                    $c->where('title', 'like', "%{$search}%");
                });
            });
        }

        // ---------- Pagination ----------
        $submissions = (clone $base)
            ->orderByDesc($gradedAtCol)
            ->paginate($perPage)
            ->appends($request->query());

        // ---------- KPIs ----------
        $allRows = (clone $base)->select('id','assignment_id','user_id','marks_awarded','is_passed','created_at', $gradedAtCol)->get();

        $totalGraded = $allRows->count();
        $uniqueStudents = $allRows->pluck('user_id')->unique()->count();
        $uniqueAssignments = $allRows->pluck('assignment_id')->unique()->count();

        $passCount = $allRows->where('is_passed', true)->count();
        $failCount = $allRows->where('is_passed', false)->count();

        // avg percent only for points assignments where total_marks > 0
        $pointsRows = (clone $base)
            ->whereHas('assignment', fn($q) => $q->where('grading_type','points')->where('total_marks','>',0))
            ->with('assignment:id,grading_type,total_marks')
            ->get();

        $avgPercent = 0;
        if ($pointsRows->count() > 0) {
            $sum = 0;
            $n = 0;
            foreach ($pointsRows as $r) {
                $total = (int)($r->assignment?->total_marks ?? 0);
                $mark  = (int)($r->marks_awarded ?? 0);
                if ($total > 0) {
                    $sum += (int) round(($mark / $total) * 100);
                    $n++;
                }
            }
            $avgPercent = $n > 0 ? (int) round($sum / $n) : 0;
        }

        $passRate = ($passCount + $failCount) > 0
            ? (int) round(($passCount / ($passCount + $failCount)) * 100)
            : 0;

        // turnaround hours
        $avgTurnaroundHrs = 0;
        if ($totalGraded > 0) {
            $sumHrs = 0;
            $n = 0;
            foreach ($allRows as $r) {
                $gradedAt = $r->{$gradedAtCol} ?? null;
                if ($gradedAt) {
                    $hrs = \Carbon\Carbon::parse($gradedAt)->diffInHours(\Carbon\Carbon::parse($r->created_at));
                    $sumHrs += $hrs; $n++;
                }
            }
            $avgTurnaroundHrs = $n > 0 ? (int) round($sumHrs / $n) : 0;
        }

        $kpis = compact(
            'totalGraded','uniqueStudents','uniqueAssignments',
            'passCount','failCount','passRate','avgPercent','avgTurnaroundHrs'
        );

        // ---------- Charts ----------
        // 1) Grade distribution (percent buckets) for points assignments only
        $buckets = [
            '0-39' => 0, '40-59' => 0, '60-79' => 0, '80-100' => 0
        ];

        foreach ($pointsRows as $r) {
            $total = (int)($r->assignment?->total_marks ?? 0);
            $mark  = (int)($r->marks_awarded ?? 0);
            if ($total <= 0) continue;

            $p = (int) round(($mark / $total) * 100);
            if ($p < 40) $buckets['0-39']++;
            elseif ($p < 60) $buckets['40-59']++;
            elseif ($p < 80) $buckets['60-79']++;
            else $buckets['80-100']++;
        }

        // 2) Trend (last 14 days) - counts per day
        $trendDays = 14;
        $trendFrom = now()->subDays($trendDays - 1)->startOfDay();

        $trend = (clone $base)
            ->where("$subTable.$gradedAtCol", '>=', $trendFrom) // ✅ FIX
            ->select(
                DB::raw("DATE($subTable.$gradedAtCol) as d"),    // ✅ FIX
                DB::raw('COUNT(*) as c')
            )
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd');

        $trendLabels = [];
        $trendCounts = [];
        for ($i = $trendDays - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $trendLabels[] = \Carbon\Carbon::parse($day)->format('d M');
            $trendCounts[] = (int)($trend[$day] ?? 0);
        }

        // 3) Top courses by graded count (Top 10)
        $topCourses = (clone $base)
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->select('courses.title as course_title', DB::raw('COUNT(*) as c'))
            ->groupBy('courses.id','courses.title')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        $topCourseLabels = $topCourses->pluck('course_title')->toArray();
        $topCourseCounts = $topCourses->pluck('c')->map(fn($v)=>(int)$v)->toArray();

        $charts = [
            'distLabels' => array_keys($buckets),
            'distValues' => array_values($buckets),

            'passLabels' => ['Pass','Fail'],
            'passValues' => [(int)$passCount, (int)$failCount],

            'trendLabels' => $trendLabels,
            'trendCounts' => $trendCounts,

            'topCourseLabels' => $topCourseLabels,
            'topCourseCounts' => $topCourseCounts,
        ];

        return view('admin.assignments.graded', compact(
            'submissions',
            'divisions','subjects','coursesList',
            'divisionId','subjectId','courseId',
            'rangeDays','status','type','search','perPage',
            'kpis','charts'
        ));
    }
}