<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function index(Course $course)
    {
        $lessons = $course->lessons()->orderBy('position')->paginate(15);
        return view('admin.lessons.index', compact('course', 'lessons'));
    }

    public function create(Course $course)
    {
        return view('admin.lessons.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'position' => ['required', 'integer', 'min:1'],

            // blocks
            'blocks' => ['nullable', 'array'],
            'blocks.*.type' => ['required_with:blocks', 'in:text,video,file'],
            'blocks.*.text' => ['nullable', 'string'],
            'blocks.*.video_url' => ['nullable', 'string', 'max:500'],
            'blocks.*.file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip'],
        ]);

        $blocks = $this->normalizeBlocks($request);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'position' => $validated['position'],
            'content_blocks' => $blocks,
        ]);

        return redirect()
            ->route('admin.courses.lessons.index', $course->id)
            ->with('success', 'Lesson created successfully.');
    }

    public function edit(Course $course, Lesson $lesson)
    {
        abort_if($lesson->course_id !== $course->id, 404);
        return view('admin.lessons.edit', compact('course', 'lesson'));
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        abort_if($lesson->course_id !== $course->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'position' => ['required', 'integer', 'min:1'],

            'blocks' => ['nullable', 'array'],
            'blocks.*.type' => ['required_with:blocks', 'in:text,video,file'],
            'blocks.*.text' => ['nullable', 'string'],
            'blocks.*.video_url' => ['nullable', 'string', 'max:500'],
            'blocks.*.file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip'],
            'blocks.*.existing_path' => ['nullable', 'string'],
            'blocks.*.remove_file' => ['nullable', 'boolean'],
        ]);

        $blocks = $this->normalizeBlocks($request, $lesson);

        $lesson->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'position' => $validated['position'],
            'content_blocks' => $blocks,
        ]);

        return redirect()
            ->route('admin.courses.lessons.index', $course->id)
            ->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        abort_if($lesson->course_id !== $course->id, 404);

        // delete stored block files
        foreach (($lesson->content_blocks ?? []) as $b) {
            if (($b['type'] ?? null) === 'file' && !empty($b['path'])) {
                Storage::disk('public')->delete($b['path']);
            }
        }

        $lesson->delete();

        return redirect()
            ->route('admin.courses.lessons.index', $course->id)
            ->with('success', 'Lesson deleted successfully.');
    }

    private function normalizeBlocks(Request $request, ?Lesson $lesson = null): array
    {
        $blocksInput = $request->input('blocks', []);
        $result = [];

        foreach ($blocksInput as $i => $block) {
            $type = $block['type'] ?? null;
            if (!$type) continue;

            if ($type === 'text') {
                $text = trim((string)($block['text'] ?? ''));
                if ($text === '') continue;

                $result[] = [
                    'type' => 'text',
                    'text' => $text,
                ];
            }

            if ($type === 'video') {
                $url = trim((string)($block['video_url'] ?? ''));
                if ($url === '') continue;

                $result[] = [
                    'type' => 'video',
                    'video_url' => $url,
                ];
            }

            if ($type === 'file') {
                $remove = isset($block['remove_file']) && (string)$block['remove_file'] === '1';

                $existingPath = $block['existing_path'] ?? null;
                $newPath = $existingPath;

                // remove old file if requested
                if ($remove && $existingPath) {
                    Storage::disk('public')->delete($existingPath);
                    $newPath = null;
                }

                // upload new file
                if ($request->hasFile("blocks.$i.file")) {
                    // delete old if replaced
                    if ($existingPath) Storage::disk('public')->delete($existingPath);

                    $newPath = $request->file("blocks.$i.file")->store('lesson_blocks', 'public');
                }

                if (!$newPath) continue;

                $result[] = [
                    'type' => 'file',
                    'path' => $newPath,
                ];
            }
        }

        return $result;
    }
}