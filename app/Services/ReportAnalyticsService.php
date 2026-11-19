<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportAnalyticsService
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function dateRange(Request $request, int $defaultDays = 30): array
    {
        $from = $request->date('from') ?? now()->subDays($defaultDays);
        $to = $request->date('to') ?? now();

        return [
            $from->copy()->startOfDay(),
            $to->copy()->endOfDay(),
        ];
    }

    public function orderReport(Carbon $from, Carbon $to): array
    {
        $base = Order::query()->whereBetween('created_at', [$from, $to]);

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total_orders' => (clone $base)->count(),
            'delivered' => (int) ($byStatus[OrderStatus::Delivered->value] ?? 0),
            'cancelled' => (int) ($byStatus[OrderStatus::Cancelled->value] ?? 0),
            'pending_pipeline' => (clone $base)->whereNotIn('status', [
                OrderStatus::Delivered->value,
                OrderStatus::Cancelled->value,
                OrderStatus::Rejected->value,
                OrderStatus::Failed->value,
            ])->count(),
            'by_status' => $byStatus,
            'orders' => Order::query()
                ->with(['restaurant:id,name', 'user:id,name'])
                ->whereBetween('created_at', [$from, $to])
                ->latest()
                ->paginate(admin_listing_per_page())
                ->withQueryString(),
        ];
    }

    public function revenueReport(Carbon $from, Carbon $to): array
    {
        $delivered = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', OrderStatus::Delivered);

        $salesByDay = (clone $delivered)
            ->selectRaw('DATE(created_at) as day, SUM(grand_total) as total, COUNT(*) as orders_count')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $totals = (clone $delivered)
            ->selectRaw('
                COALESCE(SUM(grand_total), 0) as gross_revenue,
                COALESCE(SUM(commission_amount), 0) as commission_total,
                COALESCE(SUM(restaurant_earning_amount), 0) as restaurant_earnings,
                COALESCE(SUM(delivery_charge), 0) as delivery_fees,
                COUNT(*) as order_count
            ')
            ->first();

        $byPayment = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', OrderStatus::Delivered)
            ->select('payment_method', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(grand_total) as revenue'))
            ->groupBy('payment_method')
            ->get();

        return compact('salesByDay', 'totals', 'byPayment');
    }

    public function userActivityReport(Carbon $from, Carbon $to): array
    {
        $newUsers = User::query()->whereBetween('created_at', [$from, $to])->count();

        $activeCustomers = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->distinct('user_id')
            ->count('user_id');

        $topCustomers = Order::query()
            ->select(
                'user_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(grand_total) as spend')
            )
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('spend')
            ->with('user:id,name,email,phone')
            ->limit(15)
            ->get();

        $registrationsByDay = User::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return compact('newUsers', 'activeCustomers', 'topCustomers', 'registrationsByDay');
    }

    public function restaurantPerformanceReport(Carbon $from, Carbon $to): array
    {
        $delivered = OrderStatus::Delivered->value;

        $rows = Order::query()
            ->select('restaurant_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw("SUM(CASE WHEN status = '{$delivered}' THEN 1 ELSE 0 END) as delivered_count")
            ->selectRaw("SUM(CASE WHEN status = '{$delivered}' THEN grand_total ELSE 0 END) as revenue")
            ->selectRaw("AVG(CASE WHEN status = '{$delivered}' THEN grand_total END) as avg_order_value")
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('restaurant_id')
            ->orderByDesc('revenue')
            ->with('restaurant:id,name')
            ->limit(25)
            ->get();

        return ['restaurants' => $rows];
    }

    public function deliveryPartnerPerformanceReport(Carbon $from, Carbon $to): array
    {
        $delivered = OrderStatus::Delivered->value;

        $partners = DeliveryAssignment::query()
            ->select('delivery_assignments.delivery_partner_id')
            ->selectRaw('COUNT(*) as assignments')
            ->selectRaw("SUM(CASE WHEN orders.status = '{$delivered}' THEN 1 ELSE 0 END) as completed_deliveries")
            ->join('orders', 'orders.id', '=', 'delivery_assignments.order_id')
            ->whereBetween('delivery_assignments.created_at', [$from, $to])
            ->groupBy('delivery_assignments.delivery_partner_id')
            ->orderByDesc('completed_deliveries')
            ->with('deliveryPartner:id,name,phone')
            ->limit(25)
            ->get();

        return ['partners' => $partners];
    }

    public function popularItemsAndRestaurants(Carbon $from, Carbon $to): array
    {
        $topItems = OrderItem::query()
            ->select(
                'food_item_id',
                'item_name',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to])
                    ->where('status', OrderStatus::Delivered);
            })
            ->groupBy('food_item_id', 'item_name')
            ->orderByDesc('units_sold')
            ->limit(15)
            ->get();

        $topRestaurants = Order::query()
            ->select('restaurant_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(grand_total) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->where('status', OrderStatus::Delivered)
            ->groupBy('restaurant_id')
            ->orderByDesc('revenue')
            ->with('restaurant:id,name')
            ->limit(15)
            ->get();

        return compact('topItems', 'topRestaurants');
    }

    /**
     * @return Collection<int, object{day: string, orders_count: int, revenue: float}>
     */
    public function orderTrends(Carbon $from, Carbon $to): Collection
    {
        return Order::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders_count, SUM(grand_total) as revenue')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }
}
