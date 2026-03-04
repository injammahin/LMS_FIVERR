@extends('layouts.staff')
@section('title', 'Assignment Submission')
@section('page_title', 'Assignment Submission')

@section('content')
    @php
        use Illuminate\Support\Carbon;
        use Illuminate\Support\Facades\Route;

        $course = $assignment?->course;

        $gradingType = $assignment->grading_type ?? 'points'; // points|pass_fail
        $totalMarks = (int) ($assignment->total_marks ?? 0);
        $awarded = is_null($submission->marks_awarded) ? null : (int) $submission->marks_awarded;

        $percent = 0;
        if ($gradingType === 'points' && $awarded !== null && $totalMarks > 0) {
            $percent = (int) round(($awarded / $totalMarks) * 100);
        }

        $status = (string) ($submission->status ?? 'submitted');
        $isGraded = $status === 'graded';

        // ✅ Back URLs (staff)
        $backToSubmissions = Route::has('staff.submissions.index')
            ? route('staff.submissions.index', ['type' => 'assignment'])
            : url()->previous();

        $backToCourse = ($course && Route::has('staff.courses.show'))
            ? route('staff.courses.show', $course->id)
            : null;

        // ✅ PDF route name compatibility:
        // If you later move routes inside staff group, you may have staff.assignments.submissions.pdf
        // Right now your route:list shows assignments.submissions.pdf (no staff prefix)
        $pdfRouteName =
            Route::has('staff.assignments.submissions.pdf') ? 'staff.assignments.submissions.pdf' :
            (Route::has('assignments.submissions.pdf') ? 'assignments.submissions.pdf' : null);

        $pdfUrl = $pdfRouteName ? route($pdfRouteName, [$assignment->id, $submission->id]) : null;

        $pill = function ($type) {
            return match ($type) {
                'graded' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'submitted' => 'bg-amber-50 text-amber-800 border-amber-200',
                'ok' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'bad' => 'bg-red-50 text-red-800 border-red-200',
                'blue' => 'bg-blue-50 text-blue-800 border-blue-200',
                default => 'bg-gray-50 text-gray-700 border-gray-200',
            };
        };

        // File preview flags
        $filePath = $submission->submission_file ?? null;
        $fileLower = $filePath ? strtolower($filePath) : '';
        $isPdf = $filePath ? str_ends_with($fileLower, '.pdf') : false;
        $isImg = $filePath ? (str_ends_with($fileLower, '.png') || str_ends_with($fileLower, '.jpg') || str_ends_with($fileLower, '.jpeg') || str_ends_with($fileLower, '.webp')) : false;

        $submittedAt = $submission->submitted_at ?? $submission->created_at ?? null;
    @endphp

    @once
        <style>
            .card {
                border: 1px solid #e5e7eb;
                background: #fff;
                border-radius: 18px;
                box-shadow: 0 12px 28px rgba(0, 0, 0, .06)
            }

            .cardHead {
                padding: 16px 18px;
                border-bottom: 1px solid #e5e7eb;
                background: linear-gradient(to bottom, #fafafa, #fff)
            }

            .cardBody {
                padding: 16px 18px
            }

            .chip {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 7px 12px;
                border-radius: 999px;
                border: 1px solid;
                font-size: 12px;
                font-weight: 800;
                white-space: nowrap
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 14px;
                border-radius: 14px;
                border: 1px solid #e5e7eb;
                background: #fff;
                font-weight: 800;
                font-size: 13px
            }

            .btn:hover {
                background: #f9fafb
            }

            .btnPrimary {
                background: #111827;
                color: #fff;
                border-color: #111827
            }

            .btnPrimary:hover {
                background: #0b1220
            }

            .muted {
                color: #6b7280
            }

            .mono {
                font-variant-numeric: tabular-nums
            }
        </style>
    @endonce

    <div class="space-y-6">

        {{-- HERO --}}
        <div class="card overflow-hidden">
            <div class="h-14 bg-gradient-to-r from-amber-600 via-orange-600 to-rose-600 relative">
                <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_30%_30%,white,transparent_50%)]">
                </div>
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_70%,white,transparent_45%)]">
                </div>
            </div>

            <div class="cardBody mt-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

                    <div class="min-w-0">
                        <div class="chip {{ $isGraded ? $pill('graded') : $pill('submitted') }}">
                            <i class="fa-solid fa-file-pen text-[12px]"></i>
                            {{ $isGraded ? 'Graded' : 'Submitted' }}
                        </div>

                        <h1 class="mt-3 text-lg sm:text-xl font-extrabold text-gray-900 truncate">
                            {{ $assignment->title }}
                        </h1>

                        <div class="mt-1 text-sm text-gray-600">
                            Course: <span class="font-semibold text-gray-900">{{ $course?->title ?? '—' }}</span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="chip {{ $pill('blue') }}">
                                <i class="fa-solid fa-user-graduate text-[12px]"></i>
                                {{ $submission->user?->name ?? '—' }}
                            </span>

                            <span class="chip {{ $pill('blue') }}">
                                <i class="fa-solid fa-clock text-[12px]"></i>
                                Submitted: {{ $submittedAt ? Carbon::parse($submittedAt)->format('d M Y, h:i A') : '—' }}
                            </span>

                            <span class="chip {{ $pill('submitted') }}">
                                <i class="fa-solid fa-eye text-[12px]"></i>
                                Staff view-only
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ $backToSubmissions }}" class="btn">
                            <i class="fa-solid fa-arrow-left"></i> Back to Submissions
                        </a>

                        @if($backToCourse)
                            <a href="{{ $backToCourse }}" class="btn">
                                <i class="fa-solid fa-book-open"></i> Course
                            </a>
                        @endif

                        @if($pdfUrl)
                            <a href="{{ $pdfUrl }}" class="btn btnPrimary">
                                <i class="fa-solid fa-file-pdf"></i> Download PDF
                            </a>
                        @endif
                    </div>

                </div>

                {{-- KPI ROW --}}
                <div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <div class="card !shadow-none !border-gray-200">
                        <div class="cardBody">
                            <div class="text-xs muted">Assignment Info</div>
                            <div class="mt-2 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <div class="text-xs muted">Grading</div>
                                    <div class="font-extrabold text-gray-900">
                                        {{ $gradingType === 'pass_fail' ? 'Pass / Fail' : 'Points' }}
                                    </div>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <div class="text-xs muted">Total Marks</div>
                                    <div class="font-extrabold text-gray-900 mono">
                                        {{ $totalMarks > 0 ? $totalMarks : '—' }}
                                    </div>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 col-span-2">
                                    <div class="text-xs muted">Due Date</div>
                                    <div class="font-semibold text-gray-900">
                                        {{ optional($assignment->due_at)->format('d M Y, h:i A') ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card !shadow-none !border-gray-200">
                        <div class="cardBody">
                            <div class="text-xs muted">Current Result</div>
                            <div class="mt-1 text-lg font-extrabold text-gray-900">
                                {{ $isGraded ? 'Graded' : 'Not graded yet' }}
                            </div>

                            <div class="mt-3 text-sm text-gray-700 space-y-2">
                                @if($gradingType === 'pass_fail')
                                    <div>
                                        Pass/Fail:
                                        <span class="font-semibold text-gray-900">
                                            {{ is_null($submission->is_passed) ? '—' : ($submission->is_passed ? 'Passed' : 'Failed') }}
                                        </span>
                                    </div>
                                @else
                                    <div>
                                        Marks:
                                        <span class="font-semibold text-gray-900 mono">
                                            {{ is_null($awarded) ? '—' : $awarded }}
                                        </span>
                                        <span class="text-gray-500">/ {{ $totalMarks > 0 ? $totalMarks : '—' }}</span>
                                    </div>

                                    @if($isGraded && !is_null($awarded) && $totalMarks > 0)
                                        <div class="mt-2">
                                            <span class="chip {{ $percent >= 50 ? $pill('ok') : $pill('bad') }}">
                                                <i class="fa-solid fa-chart-pie text-[12px]"></i>
                                                {{ $percent }}%
                                            </span>
                                        </div>

                                        <div class="mt-2 h-2.5 rounded-full bg-gray-100 overflow-hidden border border-gray-200">
                                            <div class="h-2.5 rounded-full" style="width: {{ $percent }}%; background:#10b981;">
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <div>
                                    Feedback:
                                    <span class="font-semibold text-gray-900">
                                        {{ $submission->feedback ? 'Added' : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card !shadow-none !border-gray-200">
                        <div class="cardBody">
                            <div class="text-xs muted">Submission Type</div>
                            <div class="mt-1 text-lg font-extrabold text-gray-900">
                                {{ strtoupper($assignment->submission_type ?? 'mixed') }}
                            </div>
                            <div class="mt-2 text-sm muted">
                                Staff can view submission content & status, but cannot grade.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        {{-- SUBMITTED CONTENT --}}
        <div class="card overflow-hidden">
            <div class="cardHead flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-extrabold text-gray-900">Student Submission</div>
                    <div class="text-xs muted mt-1">Text answer and/or uploaded file (read-only)</div>
                </div>

                <span class="chip {{ $pill('blue') }}">
                    <i class="fa-solid fa-paperclip text-[12px]"></i>
                    {{ strtoupper($assignment->submission_type ?? 'mixed') }}
                </span>
            </div>

            <div class="cardBody space-y-5">

                {{-- Text submission --}}
                @if(!empty($submission->submission_text))
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="text-sm font-extrabold text-gray-900 mb-2">Text Answer</div>
                        <div class="text-sm text-gray-800 whitespace-pre-line">
                            {{ $submission->submission_text }}
                        </div>
                    </div>
                @endif

                {{-- File submission --}}
                @if(!empty($filePath))
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="text-sm font-extrabold text-gray-900 mb-3">Submitted File</div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="btn btnPrimary">
                                <i class="fa-solid fa-download"></i> Download
                            </a>

                            <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="btn">
                                <i class="fa-solid fa-eye"></i> Preview
                            </a>

                            <span class="text-xs muted break-all">
                                {{ $filePath }}
                            </span>
                        </div>

                        {{-- Inline preview --}}
                        @if($isImg)
                            <div class="mt-4">
                                <img src="{{ asset('storage/' . $filePath) }}"
                                    class="max-h-96 rounded-2xl border border-gray-200 shadow-sm" alt="Submission image">
                            </div>
                        @elseif($isPdf)
                            <div class="mt-4 rounded-2xl overflow-hidden border border-gray-200 bg-white">
                                <iframe src="{{ asset('storage/' . $filePath) }}" class="w-full h-[520px]"></iframe>
                            </div>
                        @endif
                    </div>
                @endif

                @if(empty($submission->submission_text) && empty($filePath))
                    <div class="text-gray-500 text-sm">
                        No submission content found.
                    </div>
                @endif

            </div>
        </div>

        {{-- FEEDBACK (read-only) --}}
        <div class="card overflow-hidden">
            <div class="cardHead">
                <div class="text-sm font-extrabold text-gray-900">Teacher Feedback</div>
                <div class="text-xs muted mt-1">If grading is completed, feedback will appear here.</div>
            </div>

            <div class="cardBody">
                @if(!empty($submission->feedback))
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm text-gray-800 whitespace-pre-line">
                        {{ $submission->feedback }}
                    </div>
                @else
                    <div class="text-sm text-gray-500">No feedback yet.</div>
                @endif
            </div>
        </div>

    </div>
@endsection