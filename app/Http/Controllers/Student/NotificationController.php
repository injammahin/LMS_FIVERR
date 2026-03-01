<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!Schema::hasTable('notifications')) {
            return view('student.notifications.index', [
                'notifications' => collect(),
                'filter' => 'all',
                'noDb' => true,
            ]);
        }

        $filter = $request->get('filter', 'all'); // all | unread

        $q = $user->notifications()->latest();
        if ($filter === 'unread') {
            $q = $user->unreadNotifications()->latest();
        }

        $notifications = $q->paginate(20)->withQueryString();

        return view('student.notifications.index', compact('notifications', 'filter'));
    }

    public function read(string $id)
    {
        $user = auth()->user();
        abort_unless(Schema::hasTable('notifications'), 404);

        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function unread(string $id)
    {
        $user = auth()->user();
        abort_unless(Schema::hasTable('notifications'), 404);

        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->read_at = null;
        $n->save();

        return back()->with('success', 'Notification marked as unread.');
    }

    public function readAll()
    {
        $user = auth()->user();
        abort_unless(Schema::hasTable('notifications'), 404);

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}