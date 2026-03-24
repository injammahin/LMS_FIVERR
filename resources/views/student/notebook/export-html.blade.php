<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $note->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.7;
            color: #111827;
            padding: 32px;
        }

        h1 {
            margin-bottom: 8px;
        }

        .meta {
            color: #6b7280;
            margin-bottom: 24px;
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <h1>{{ $note->title }}</h1>
    <div class="meta">
        Subject: {{ $note->subject->name ?? 'General' }} |
        Course: {{ $note->course->title ?? 'General' }}
    </div>

    {!! $note->body_html !!}
</body>

</html>