<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\SettlementStatus;
use App\Models\Order;
use App\Models\RestaurantEarning;
use App\Models\Settlement;
use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Builder;

class RestaurantDashboardService
{
    public function __construct(
        private int $restaurantId,
        private int $restaurantUserId,
    ) {}

    public static function forAuthenticatedRestaurant(): self
    {
        $user = auth('restaurant')->user();

        return new self(
            (int) $user->restaurant_id,
            (int) $user->id,
        );
    }

    private function orders(): Builder
    {
        return Order::query()->where('restaurant_id', $this->restaurantId);
    }

    private function earnings(): Builder
    {
        return RestaurantEarning::query()->where('restaurant_id', $this->restaurantId);
    }

    /**
     * @return array<string, mixed>
     */
    public function overviewStats(): array
    {
        $newOrders = (int) $this->orders()->where('status', OrderStatus::Pending)->count();
        $ongoingOrders = (int) $this->orders()->whereIn('status', OrderStatus::restaurantOngoingValues())->count();
        $completedOrders = (int) $this->orders()->where('status', OrderStatus::Delivered)->count();
        $cancelledOrders = (int) $this->orders()->whereIn('status', OrderStatus::restaurantTerminalNegativeValues())->count();

        $openSupportTickets = (int) SupportTicket::query()
            ->where('creator_type', 'restaurant_user')
            ->where('creator_id', $this->restaurantUserId)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $pendingSettlementAmount = (float) $this->earnings()->where('status', 'pending')->sum('net_earning');
        $settledEarnings = (float) $this->earnings()->where('status', 'settled')->sum('net_earning');

        $activeSettlements = (int) Settlement::query()
            ->where('restaurant_id', $this->restaurantId)
            ->whereIn('status', [SettlementStatus::Pending, SettlementStatus::Processing, SettlementStatus::OnHold])
            ->count();

        $deliveredSalesQuery = fn () => $this->orders()->where('status', OrderStatus::Delivered);

        return [
            'total_orders' => (int) $this->orders()->count(),
            'new_orders' => $newOrders,
            'ongoing_orders' => $ongoingOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'pending_requests' => $newOrders + $openSupportTickets,
            'open_support_tickets' => $openSupportTickets,
            'daily_sales' => (float) $deliveredSalesQuery()->whereDate('created_at', today())->sum('grand_total'),
            'monthly_sales' => (float) $deliveredSalesQuery()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('grand_total'),
            'total_sales' => (float) $deliveredSalesQuery()->sum('grand_total'),
            'total_earnings' => (float) $this->earnings()->sum('net_earning'),
            'pending_earnings' => $pendingSettlementAmount,
            'settled_earnings' => $settledEarnings,
            'platform_commission' => (float) $deliveredSalesQuery()->sum('commission_amount'),
            'active_settlements' => $activeSettlements,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Order>
     */
    public function recentOrders(int $limit = 10)
    {
        return $this->orders()->latest()->limit($limit)->get();
    }
}
