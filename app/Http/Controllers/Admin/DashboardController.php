<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\RestaurantStatus;
use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantEarning;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use AppliesAdminGeoScope;

    public function index()
    {
        $pendingOrders = $this->scopeOrders(Order::query())->where('status', OrderStatus::Pending)->count();
        $pendingRestaurants = $this->scopeRestaurants(Restaurant::query())->where('status', RestaurantStatus::Pending)->count();
        $pendingPartners = DeliveryPartner::query()->where('status', 'pending')->count();

        $stats = [
            'total_users' => User::query()->count(),
            'total_restaurants' => $this->scopeRestaurants(Restaurant::query())->count(),
            'total_delivery_partners' => DeliveryPartner::query()->count(),
            'total_orders' => $this->scopeOrders(Order::query())->count(),
            'active_restaurants' => $this->scopeRestaurants(Restaurant::query())
                ->where('status', RestaurantStatus::Approved)
                ->where('is_blocked', false)
                ->count(),
            'pending_orders' => $pendingOrders,
            'pending_restaurants' => $pendingRestaurants,
            'pending_partners' => $pendingPartners,
            'pending_requests' => $pendingOrders + $pendingRestaurants + $pendingPartners,
            'total_revenue' => (float) $this->scopeOrders(Order::query())
                ->where('status', OrderStatus::Delivered)
                ->sum('grand_total'),
            'platform_commission' => (float) $this->scopeOrders(Order::query())->sum('commission_amount'),
            'pending_settlements' => (float) RestaurantEarning::query()
                ->where('status', 'pending')
                ->whereIn('restaurant_id', $this->scopeRestaurants(Restaurant::query())->select('id'))
                ->sum('net_earning'),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function orderActivities(Request $request)
    {
        $query = $this->scopeOrders(Order::query())
            ->with(['restaurant', 'user'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('days')) {
            $query->where('created_at', '>=', now()->subDays((int) $request->days));
        }

        return response()->json([
            'data' => $query->limit(100)->get()->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'restaurant' => $order->restaurant?->name,
                'customer' => $order->customer_name ?? $order->user?->name,
                'datetime' => $order->created_at?->format('d M Y \a\t H:i'),
                'items' => $order->items()->count(),
                'amount' => money_inr($order->grand_total),
                'status' => $order->status->label(),
                'status_value' => $order->status->value,
            ]),
        ]);
    }

    public function chartData(Request $request)
    {
        $months = (int) $request->get('months', 6);
        $start = now()->subMonths($months - 1)->startOfMonth();

        $rows = $this->scopeOrders(Order::query())
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, SUM(grand_total) as total, COUNT(*) as count')
            ->where('created_at', '>=', $start)
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json(['series' => $rows]);
    }
}
