@php
    $total = (int) ($attempt->total_marks ?? $attempt->total ?? 0);
    $score = (int) ($attempt->score ?? 0);
    $pct = $total > 0 ? (int) round(($score / $total) * 100) : 0;

    $passMark = (int) ($attempt->quiz?->pass_mark ?? 0);
    $passed = $total > 0 ? ($pct >= $passMark) : null;

    // Convert Quill HTML to safe PDF HTML:
    // - make /storage images absolute
    // - remove iframes (dompdf doesn't render them)
    $renderQuestion = function ($html) {
        $html = $html ?? '';
        $html = preg_replace('/<iframe[\s\S]*?<\/iframe>/', '<div style="color:#6b7280;font-size:12px;">[Embedded video not shown in PDF]</div>', $html);

        $base = config('app.url'); // must be set correctly in .env (APP_URL)
        $html = str_replace('src="/storage/', 'src="' . $base . '/storage/', $html);
        $html = str_replace("src='/storage/", "src='" . $base . "/storage/", $html);

        return $html;
    };
@endphp

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Quiz Attempt PDF</title>
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

        .row {
            width: 100%;
        }

        .row td {
            vertical-align: top;
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        .ok {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }

        .bad {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .q {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f1f5f9;
        }

        img {
            max-width: 100%;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            margin: 8px 0;
        }

        .qhtml p {
            margin: 6px 0;
        }

        .qhtml ul {
            margin: 6px 0;
            padding-left: 18px;
        }

        .qhtml ol {
            margin: 6px 0;
            padding-left: 18px;
        }

        .small {
            font-size: 11px;
        }
    </style>
</head>

<body>

    <div class="box">
        <div class="title">{{ $attempt->quiz?->title ?? 'Quiz' }}</div>
        <div class="muted">Course: {{ $attempt->quiz?->course?->title ?? '—' }}</div>
        <div class="muted small">Exported for: {{ ucfirst($viewerRole ?? 'viewer') }} • Attempt ID: {{ $attempt->id }}
        </div>
    </div>

    <table class="row" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width:55%; padding-right:10px;">
                <div class="box">
                    <div><strong>Student:</strong> {{ $attempt->user?->name ?? '—' }}</div>
                    <div class="muted">{{ $attempt->user?->username ?? $attempt->user?->email ?? '—' }}</div>
                    <div class="muted small">Started:
                        {{ optional($attempt->started_at)->format('d M Y, h:i A') ?? '—' }}</div>
                    <div class="muted small">Submitted:
                        {{ optional($attempt->submitted_at)->format('d M Y, h:i A') ?? '—' }}</div>
                </div>
            </td>
            <td style="width:45%;">
                <div class="box">
                    <div><strong>Score:</strong> {{ $score }} / {{ $total }}</div>
                    <div class="muted small">Percent: {{ $pct }}% • Pass mark: {{ $passMark }}%</div>
                    @if(($attempt->status ?? '') === 'graded' && $total > 0)
                        <div style="margin-top:8px;">
                            <span class="pill {{ $passed ? 'ok' : 'bad' }}">
                                {{ $passed ? 'Passed' : 'Failed' }}
                            </span>
                        </div>
                    @else
                        <div style="margin-top:8px;">
                            <span class="pill">Not graded</span>
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="box">
        <div><strong>Answers</strong> <span class="muted">(snapshot)</span></div>

        @foreach($attempt->answers as $ans)
            @php
                $q = $ans->question;
                $type = $q->type ?? 'text';
                $marks = (int) ($q->marks ?? 0);
                $payload = (array) ($ans->answer_json ?? []);
            @endphp

            <div class="q">
                <div class="muted small">
                    <strong>Type:</strong> {{ strtoupper(str_replace('_', ' ', $type)) }} •
                    <strong>Marks:</strong> {{ $marks }} •
                    <strong>Awarded:</strong> {{ is_null($ans->awarded_marks) ? 'Pending' : (int) $ans->awarded_marks }}
                </div>

                <div class="qhtml">
                    {!! $renderQuestion($q->question ?? '') !!}
                </div>

                <div style="margin-top:6px;">
                    <strong>Student Answer:</strong>
                    <div class="muted" style="margin-top:4px;">
                        @if($type === 'true_false')
                            {{ array_key_exists('value', $payload) ? ($payload['value'] ? 'True' : 'False') : '—' }}

                        @elseif($type === 'single_choice')
                            @php
                                $optId = (int) ($payload['option_id'] ?? 0);
                                $opt = $optId ? $q->options?->firstWhere('id', $optId) : null;
                            @endphp
                            {{ $opt?->option_text ?? ($optId ? "Option #{$optId}" : '—') }}

                        @elseif($type === 'multiple_choice')
                            @php
                                $ids = array_map('intval', (array) ($payload['option_ids'] ?? []));
                                $labels = $q->options?->whereIn('id', $ids)->map(fn($o) => $o->option_text ?? '—')->values()->all() ?? [];
                            @endphp
                            @if(count($labels))
                                <ul>
                                    @foreach($labels as $t)<li>{{ $t }}</li>@endforeach
                                </ul>
                            @else
                                —
                            @endif

                        @elseif($type === 'text')
                            {{ trim((string) ($payload['text'] ?? '')) !== '' ? $payload['text'] : '—' }}

                        @elseif($type === 'file')
                            @if(!empty($ans->file_path))
                                File: {{ basename($ans->file_path) }}
                            @else
                                No file uploaded.
                            @endif
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="muted small" style="text-align:center;">
        Generated on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>