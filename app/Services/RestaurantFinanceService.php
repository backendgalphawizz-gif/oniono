<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\SettlementStatus;
use App\Models\Order;
use App\Models\RestaurantEarning;
use App\Models\Settlement;
use Illuminate\Support\Collection;

class RestaurantFinanceService
{
    public function __construct(
        private int $restaurantId,
    ) {}

    public static function forAuthenticatedRestaurant(): self
    {
        return new self((int) auth('restaurant')->user()->restaurant_id);
    }

    /**
     * @return array<string, float|int>
     */
    public function overview(): array
    {
        $earningsBase = RestaurantEarning::query()->where('restaurant_id', $this->restaurantId);

        $todaySales = (float) Order::query()
            ->where('restaurant_id', $this->restaurantId)
            ->where('status', OrderStatus::Delivered)
            ->whereDate('delivered_at', today())
            ->sum('grand_total');

        $todayEarnings = (float) (clone $earningsBase)
            ->whereDate('created_at', today())
            ->sum('net_earning');

        $todayCommission = (float) (clone $earningsBase)
            ->whereDate('created_at', today())
            ->sum('commission_amount');

        $pendingEarnings = (float) (clone $earningsBase)->where('status', 'pending')->sum('net_earning');
        $settledEarnings = (float) (clone $earningsBase)->where('status', 'settled')->sum('net_earning');
        $totalCommission = (float) (clone $earningsBase)->sum('commission_amount');
        $lifetimeNet = (float) (clone $earningsBase)->sum('net_earning');
        $lifetimeSales = (float) Order::query()
            ->where('restaurant_id', $this->restaurantId)
            ->where('status', OrderStatus::Delivered)
            ->sum('grand_total');

        $pendingSettlements = (float) Settlement::query()
            ->where('restaurant_id', $this->restaurantId)
            ->whereIn('status', [SettlementStatus::Pending, SettlementStatus::Processing, SettlementStatus::OnHold])
            ->sum('amount');

        $pendingSettlementCount = (int) Settlement::query()
            ->where('restaurant_id', $this->restaurantId)
            ->whereIn('status', [SettlementStatus::Pending, SettlementStatus::Processing, SettlementStatus::OnHold])
            ->count();

        return [
            'today_sales' => $todaySales,
            'today_earnings' => $todayEarnings,
            'today_commission' => $todayCommission,
            'pending_earnings' => $pendingEarnings,
            'settled_earnings' => $settledEarnings,
            'total_commission' => $totalCommission,
            'lifetime_net' => $lifetimeNet,
            'lifetime_sales' => $lifetimeSales,
            'pending_settlement_amount' => $pendingSettlements,
            'pending_settlement_count' => $pendingSettlementCount,
        ];
    }

    /**
     * @return Collection<int, object{day: string, sales: float, earnings: float, commission: float, orders: int}>
     */
    public function dailyBreakdown(int $days = 14): Collection
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $salesRows = Order::query()
            ->where('restaurant_id', $this->restaurantId)
            ->where('status', OrderStatus::Delivered)
            ->where('delivered_at', '>=', $from)
            ->selectRaw('DATE(delivered_at) as day, SUM(grand_total) as sales, COUNT(*) as orders')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $earningsByDay = RestaurantEarning::query()
            ->where('restaurant_id', $this->restaurantId)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, SUM(net_earning) as earnings, SUM(commission_amount) as commission')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        return collect(range(0, $days - 1))->map(function (int $offset) use ($from, $salesRows, $earningsByDay) {
            $day = $from->copy()->addDays($offset)->toDateString();
            $earningRow = $earningsByDay->get($day);
            $salesRow = $salesRows->get($day);

            return (object) [
                'day' => $day,
                'sales' => (float) ($salesRow->sales ?? 0),
                'earnings' => (float) ($earningRow->earnings ?? 0),
                'commission' => (float) ($earningRow->commission ?? 0),
                'orders' => (int) ($salesRow->orders ?? 0),
            ];
        })->reverse()->values();
    }
}
