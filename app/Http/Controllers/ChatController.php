<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Notifications\NewChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Division;
use App\Models\Subject;
use App\Models\Course;
class ChatController extends Controller
{

public function users(Request $request)
{
    $query = User::query()
        ->where('id','!=',auth()->id())
        ->whereIn('role',['teacher','staff'])
        ->with('division');

    // SEARCH
    if ($request->search) {
        $query->where(function($q) use ($request){
            $q->where('name','like','%'.$request->search.'%')
              ->orWhere('email','like','%'.$request->search.'%');
        });
    }

    // ROLE FILTER
    if ($request->role) {
        $query->where('role',$request->role);
    }

    // DIVISION FILTER
    if ($request->division_id) {
        $query->where('division_id',$request->division_id);
    }

    // COURSE FILTER
    if ($request->course_id) {
        $query->whereHas('coursesTeaching', function($q) use ($request){
            $q->where('courses.id',$request->course_id);
        });
    }

    $users = $query->orderBy('name')->get();

    $divisions = Division::all();
    $courses = Course::all();

    return view('admin.chat.users', compact(
        'users',
        'divisions',
        'courses'
    ));
}

    public function chat($id)
    {
        $receiver = User::findOrFail($id);

        $messages = Message::where(function($q) use ($id){
            $q->where('sender_id',auth()->id())
              ->where('receiver_id',$id);
        })
        ->orWhere(function($q) use ($id){
            $q->where('sender_id',$id)
              ->where('receiver_id',auth()->id());
        })
        ->orderBy('id')
        ->get();

        Message::where('sender_id',$id)
            ->where('receiver_id',auth()->id())
            ->whereNull('seen_at')
            ->update(['seen_at'=>now()]);

        return view('admin.chat.chat',compact('receiver','messages'));
    }

public function send(Request $request)
{
    $request->validate([
        'receiver_id' => 'required',
        'message' => 'nullable|string',
        'file' => 'nullable|file|max:10000'
    ]);

    $filePath = null;

    if ($request->hasFile('file')) {
        $filePath = $request->file('file')->store('chat_files', 'public');
    }

    $message = Message::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $request->receiver_id,
        'message' => $request->message,
        'file' => $filePath
    ]);

    return response()->json([
        'message' => $message->message,
        'time' => $message->created_at->format('H:i'),
        'file' => $filePath
    ]);
}
    public function markSeen()
    {
        Message::where('receiver_id', auth()->id())
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}