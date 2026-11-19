<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\PanelNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = restaurant_id();
        $base = PanelNotification::query()->forRestaurant($restaurantId);

        $unreadCount = (clone $base)->unread()->count();

        $typeCounts = (clone $base)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $notifications = (clone $base)
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->boolean('unread_only'), fn ($q) => $q->unread())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $types = config('restaurant_notifications.types', []);

        return view('restaurant.notifications.index', compact(
            'notifications',
            'unreadCount',
            'typeCounts',
            'types',
        ));
    }

    public function markRead(PanelNotification $panelNotification)
    {
        abort_if($panelNotification->notifiable_type !== 'restaurant' || $panelNotification->notifiable_id !== restaurant_id(), 403);
        $panelNotification->update(['is_read' => true]);

        if ($url = $panelNotification->actionUrl()) {
            return redirect($url);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        PanelNotification::query()
            ->forRestaurant(restaurant_id())
            ->unread()
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
