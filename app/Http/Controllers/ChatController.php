<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Subject;
use App\Models\Course;

class ChatController extends Controller
{

/* ===============================
   USERS LIST
================================ */

public function users(Request $request)
{
    $authUser = auth()->user();

    $query = User::query()
        ->where('id','!=',$authUser->id)
        ->with(['division']);

    /* ===============================
       ROLE ACCESS LIMIT
    ============================== */

    if ($authUser->role == 'admin') {
        $query->whereIn('role',['teacher','staff']);
    }

    elseif ($authUser->role == 'teacher') {
        $query->whereIn('role',['admin','student']);
    }

    elseif ($authUser->role == 'student') {
        $query->where('role','teacher');
    }


    /* ===============================
       SEARCH
    ============================== */

    if ($request->filled('search')) {

        $query->where(function($q) use ($request){

            $q->where('name','like','%'.$request->search.'%')
              ->orWhere('email','like','%'.$request->search.'%');

        });
    }


    /* ===============================
       ROLE FILTER
    ============================== */

    if ($request->filled('role')) {
        $query->where('role',$request->role);
    }


    /* ===============================
       DIVISION FILTER
    ============================== */

    if ($request->filled('division_id')) {

        $query->where(function ($q) use ($request) {

            $q->whereHas('coursesTeaching.subject', function ($sub) use ($request) {
                $sub->where('division_id', $request->division_id);
            })
            ->orWhereHas('coursesSupporting.subject', function ($sub) use ($request) {
                $sub->where('division_id', $request->division_id);
            });

        });

    }


    /* ===============================
       SUBJECT FILTER
    ============================== */

    if ($request->filled('subject_id')) {

        $query->where(function ($q) use ($request) {

            $q->whereHas('coursesTeaching', function ($c) use ($request) {
                $c->where('subject_id', $request->subject_id);
            })
            ->orWhereHas('coursesSupporting', function ($c) use ($request) {
                $c->where('subject_id', $request->subject_id);
            });

        });

    }


    /* ===============================
       COURSE FILTER
    ============================== */

    if ($request->filled('course_id')) {

        $query->where(function ($q) use ($request) {

            $q->whereHas('coursesTeaching', function ($c) use ($request) {
                $c->where('courses.id', $request->course_id);
            })
            ->orWhereHas('coursesSupporting', function ($c) use ($request) {
                $c->where('courses.id', $request->course_id);
            });

        });

    }


    $users = $query->orderBy('name')->get();

    $divisions = Division::all();
    $subjects  = Subject::all();
    $courses   = Course::all();


    /* ===============================
       ROLE VIEW
    ============================== */

    if ($authUser->role == 'teacher') {

        return view('teacher.chat.users', compact(
            'users','divisions','subjects','courses'
        ));

    }

    elseif ($authUser->role == 'student') {

        return view('student.chat.users', compact(
            'users','divisions','subjects','courses'
        ));

    }

    return view('admin.chat.users', compact(
        'users','divisions','subjects','courses'
    ));
}


/* ===============================
   CHAT PAGE
================================ */

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


    /* MARK SEEN */

    Message::where('sender_id',$id)
        ->where('receiver_id',auth()->id())
        ->whereNull('seen_at')
        ->update(['seen_at'=>now()]);


    $role = auth()->user()->role;

    if($role == 'teacher'){
        return view('teacher.chat.chat',compact('receiver','messages'));
    }

    if($role == 'student'){
        return view('student.chat.chat',compact('receiver','messages'));
    }

    return view('admin.chat.chat',compact('receiver','messages'));
}



/* ===============================
   SEND MESSAGE
================================ */

public function send(Request $request)
{
    $request->validate([
        'receiver_id' => 'required',
        'message' => 'nullable|string',
        'file' => 'nullable|file|max:10000'
    ]);

    $filePath = null;

    if ($request->hasFile('file')) {

        $filePath = $request->file('file')
            ->store('chat_files', 'public');

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



/* ===============================
   MARK SEEN
================================ */

public function markSeen()
{
    Message::where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->update(['seen_at' => now()]);

    return response()->json(['status' => 'ok']);
}

}