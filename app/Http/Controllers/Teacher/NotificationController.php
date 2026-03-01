<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\TeacherCounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    use TeacherCounts;

    public function index(Request $request)
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        if (!Schema::hasTable('notifications')) {
            $sidebarCounts = $this->sidebarCounts();
            return view('teacher.notifications.index', [
                'notifications' => collect(),
                'sidebarCounts' => $sidebarCounts,
                'noDb' => true,
            ])->with('topbarUnread', $sidebarCounts['unread']);
        }

        $notifications = $teacher->notifications()
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        return view('teacher.notifications.index', compact('notifications', 'sidebarCounts'))
            ->with('topbarUnread', $unread);
    }

    public function markRead(string $id)
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        $n = $teacher->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        $teacher->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }
}