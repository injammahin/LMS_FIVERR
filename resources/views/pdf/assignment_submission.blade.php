@php
    $course = $submission->assignment?->course;
    $student = $submission->user;
@endphp

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Assignment Submission PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .muted {
            color: #6b7280;
        }

        .box {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .title {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        img {
            max-width: 100%;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin: 8px 0;
        }

        .small {
            font-size: 11px;
        }
    </style>
</head>

<body>

    <div class="box">
        <div class="title">{{ $submission->assignment?->title ?? 'Assignment' }}</div>
        <div class="muted">Course: {{ $course?->title ?? '—' }}</div>
        <div class="muted small">Exported for: {{ ucfirst($viewerRole ?? 'viewer') }} • Submission ID:
            {{ $submission->id }}</div>
    </div>

    <div class="box">
        <div><strong>Student:</strong> {{ $student?->name ?? '—' }}</div>
        <div class="muted">{{ $student?->username ?? $student?->email ?? '—' }}</div>

        <div class="muted small" style="margin-top:6px;">
            Status: <span class="pill">{{ $submission->status ?? '—' }}</span>
            • Submitted: {{ optional($submission->created_at)->format('d M Y, h:i A') ?? '—' }}
            • Updated: {{ optional($submission->updated_at)->format('d M Y, h:i A') ?? '—' }}
        </div>

        <div class="muted small" style="margin-top:6px;">
            Marks: {{ is_null($submission->marks_awarded) ? '—' : (int) $submission->marks_awarded }}
            @if(!empty($submission->assignment?->total_marks))
                / {{ (int) $submission->assignment->total_marks }}
            @endif
            • Passed: {{ is_null($submission->is_passed) ? '—' : ($submission->is_passed ? 'Yes' : 'No') }}
        </div>
    </div>

    <div class="box">
        <div><strong>Student Response</strong></div>

        @php
            // Depending on your schema: you might have text_answer / answer_text / file_path etc.
            $text = $submission->text_answer ?? $submission->answer_text ?? null;
            $file = $submission->file_path ?? $submission->attachment ?? null;
        @endphp

        @if($text)
            <div style="margin-top:8px; white-space:pre-wrap;">{{ $text }}</div>
        @else
            <div class="muted" style="margin-top:8px;">No text submitted.</div>
        @endif

        <div style="margin-top:10px;">
            <strong>File:</strong>
            @if($file)
                <span class="muted">{{ basename($file) }}</span>
            @else
                <span class="muted">No file uploaded.</span>
            @endif
        </div>
    </div>

    <div class="muted small" style="text-align:center;">
        Generated on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>