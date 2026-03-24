<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\StudentNote;
use App\Models\StudentNoteAttachment;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NotebookController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userDivision = $this->highSchoolDivisionOrAbort();

        $subjectId = $request->filled('subject_id') ? (int) $request->subject_id : null;
        $courseId = $request->filled('course_id') ? (int) $request->course_id : null;
        $noteId = $request->filled('note') ? (int) $request->note : null;

        $subjects = Subject::query()
            ->whereHas('division', fn ($q) => $q->where('level', '<=', $userDivision->level))
            ->orderBy('name')
            ->get(['id', 'name', 'division_id']);

        $courses = Course::query()
            ->whereHas('subject.division', fn ($q) => $q->where('level', '<=', $userDivision->level))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->orderBy('title')
            ->get(['id', 'subject_id', 'title']);

        $notes = StudentNote::query()
            ->with([
                'subject:id,name',
                'course:id,title',
                'attachments:id,student_note_id,file_path,original_name',
            ])
            ->where('user_id', $user->id)
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get();

        $activeNote = null;

        if ($noteId) {
            $activeNote = $notes->firstWhere('id', $noteId);
        }

        if (!$activeNote) {
            $activeNote = $notes->first();
        }

        return view('student.notebook.index', compact(
            'notes',
            'activeNote',
            'subjects',
            'courses',
            'subjectId',
            'courseId'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $userDivision = $this->highSchoolDivisionOrAbort();

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $subjectId = !empty($data['subject_id']) ? (int) $data['subject_id'] : null;
        $courseId = !empty($data['course_id']) ? (int) $data['course_id'] : null;

        if ($courseId) {
            $course = Course::with('subject.division')->findOrFail($courseId);

            abort_if(
                (int) optional(optional($course->subject)->division)->level > (int) $userDivision->level,
                403
            );

            if ($subjectId && (int) $course->subject_id !== $subjectId) {
                return back()->withErrors([
                    'course_id' => 'Selected course does not belong to the selected subject.'
                ])->withInput();
            }

            $subjectId = (int) $course->subject_id;
        }

        if ($subjectId) {
            $subject = Subject::with('division')->findOrFail($subjectId);

            abort_if(
                (int) optional($subject->division)->level > (int) $userDivision->level,
                403
            );
        }

        $note = StudentNote::create([
            'user_id' => $user->id,
            'subject_id' => $subjectId,
            'course_id' => $courseId,
            'title' => trim((string) ($data['title'] ?? '')) ?: 'Untitled Note',
            'body_html' => '',
            'body_text' => '',
            'excerpt' => null,
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'last_saved_at' => now(),
        ]);

        return redirect()->route('student.notebook.index', [
            'note' => $note->id,
            'subject_id' => $subjectId,
            'course_id' => $courseId,
        ])->with('success', 'New notebook created successfully.');
    }

    public function autosave(Request $request, StudentNote $note)
    {
        $this->authorizeNote($note);
        $this->highSchoolDivisionOrAbort();

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body_html' => ['nullable', 'string'],
            'body_text' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $bodyHtml = (string) ($data['body_html'] ?? '');
        $bodyText = trim((string) ($data['body_text'] ?? ''));
        if ($bodyText === '' && $bodyHtml !== '') {
            $bodyText = trim(strip_tags($bodyHtml));
        }

        $courseId = !empty($data['course_id']) ? (int) $data['course_id'] : $note->course_id;
        $subjectId = !empty($data['subject_id']) ? (int) $data['subject_id'] : $note->subject_id;

        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $subjectId = (int) $course->subject_id;
        }

        $excerpt = Str::limit(
            trim(preg_replace('/\s+/', ' ', $bodyText)),
            160
        );

        $note->update([
            'title' => trim((string) ($data['title'] ?? '')) ?: 'Untitled Note',
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'excerpt' => $excerpt ?: null,
            'subject_id' => $subjectId,
            'course_id' => $courseId,
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'last_saved_at' => now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'saved_at' => $note->last_saved_at?->format('h:i:s A'),
            'excerpt' => $note->excerpt,
            'title' => $note->title,
        ]);
    }

    public function uploadAttachment(Request $request, StudentNote $note)
    {
        $this->authorizeNote($note);
        $this->highSchoolDivisionOrAbort();

        $request->validate([
            'attachment' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,png,jpg,jpeg,webp,gif,zip,txt,csv,xls,xlsx,ppt,pptx'
            ],
        ]);

        $file = $request->file('attachment');
        $path = $file->store('student_note_attachments', 'public');

        $note->attachments()->create([
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Attachment uploaded successfully.');
    }

    public function destroyAttachment(StudentNoteAttachment $attachment)
    {
        $this->highSchoolDivisionOrAbort();
        $attachment->load('note');

        abort_if((int) $attachment->note->user_id !== (int) auth()->id(), 403);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed successfully.');
    }

    public function destroy(StudentNote $note)
    {
        $this->authorizeNote($note);
        $this->highSchoolDivisionOrAbort();

        foreach ($note->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $subjectId = $note->subject_id;
        $courseId = $note->course_id;

        $note->delete();

        return redirect()->route('student.notebook.index', [
            'subject_id' => $subjectId,
            'course_id' => $courseId,
        ])->with('success', 'Note deleted successfully.');
    }

    public function export(StudentNote $note, string $format)
    {
        $this->authorizeNote($note);
        $this->highSchoolDivisionOrAbort();

        $note->load(['subject:id,name', 'course:id,title']);

        $safeTitle = Str::slug($note->title ?: 'note');

        if ($format === 'txt') {
            $content = "Title: {$note->title}\n";
            $content .= "Subject: " . ($note->subject->name ?? 'General') . "\n";
            $content .= "Course: " . ($note->course->title ?? 'General') . "\n\n";
            $content .= (string) $note->body_text;

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, "{$safeTitle}.txt", [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        if ($format === 'html') {
            $content = view('student.notebook.export-html', compact('note'))->render();

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, "{$safeTitle}.html", [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        abort(404);
    }

    private function authorizeNote(StudentNote $note): void
    {
        abort_if((int) $note->user_id !== (int) auth()->id(), 403);
    }

    private function highSchoolDivisionOrAbort(): Division
    {
        $user = auth()->user();
        $userDivision = Division::find($user->division_id);

        abort_if(!$userDivision, 403);
        abort_if(!$this->isHighSchoolDivision($userDivision), 403);

        return $userDivision;
    }

    private function isHighSchoolDivision(?Division $division): bool
    {
        if (!$division) {
            return false;
        }

        $name = strtolower((string) $division->name);

        if (str_contains($name, 'high')) {
            return true;
        }

        $levels = Division::orderBy('level')->pluck('level')->values();

        if ($levels->count() < 3) {
            return false;
        }

        $middleLevel = (int) $levels[1];

        return (int) $division->level > $middleLevel;
    }
}