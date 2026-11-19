<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PanelNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = PanelNotification::query()
            ->forAdmin()
            ->where('notifiable_id', auth('admin')->id())
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markRead(PanelNotification $panelNotification)
    {
        abort_if($panelNotification->notifiable_type !== 'admin' || $panelNotification->notifiable_id !== auth('admin')->id(), 403);
        $panelNotification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }
}
