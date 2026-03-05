<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncKbUploadToOpenAI;
use App\Models\AiKbFile;
use App\Models\Course;
use Illuminate\Http\Request;

class AiTrainingFileController extends Controller
{
    public function index()
    {
        $files = AiKbFile::latest()->paginate(20);
        return view('admin.ai_assistant.files.index', compact('files'));
    }

    public function create()
    {
        $courses = class_exists(Course::class) ? Course::orderBy('id','desc')->get() : collect();
        return view('admin.ai_assistant.files.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scope' => ['required','in:global,course'],
            'course_id' => ['nullable','integer'],
            'file' => ['required','file','max:51200'], // 50MB (OpenAI file inputs limit varies by usage; keep safe)
        ]);

        if ($data['scope'] === 'global') $data['course_id'] = null;

        $up = $request->file('file');
        $path = $up->store('ai_kb_uploads', 'local');

        $kbFile = AiKbFile::create([
            'scope' => $data['scope'],
            'course_id' => $data['course_id'],
            'original_name' => $up->getClientOriginalName(),
            'stored_path' => $path,
            'mime' => $up->getClientMimeType(),
            'size' => $up->getSize(),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        SyncKbUploadToOpenAI::dispatch($kbFile->id);

        return redirect()->route('admin.ai.files.index')->with('success', 'File uploaded & syncing to AI.');
    }
}