<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    private const RESTAURANT_TRANSITIONS = [
        OrderStatus::Pending->value => [OrderStatus::Accepted, OrderStatus::Rejected],
        OrderStatus::Accepted->value => [OrderStatus::Preparing],
        OrderStatus::Preparing->value => [OrderStatus::ReadyForPickup],
    ];

    /** Statuses where admin may cancel (before rider pickup). */
    private const ADMIN_CANCELLABLE = [
        OrderStatus::Pending,
        OrderStatus::Accepted,
        OrderStatus::Preparing,
    ];

    private const ADMIN_TRANSITIONS = [
        OrderStatus::Pending->value => [OrderStatus::Cancelled],
        OrderStatus::Accepted->value => [OrderStatus::Cancelled],
        OrderStatus::Preparing->value => [OrderStatus::Cancelled],
        OrderStatus::ReadyForPickup->value => [OrderStatus::PickedUp],
        OrderStatus::PickedUp->value => [OrderStatus::OnTheWay],
        OrderStatus::OnTheWay->value => [OrderStatus::Delivered],
    ];

    public function __construct(
        private CommissionService $commissionService
    ) {}

    public function canAdminCancel(Order $order): bool
    {
        return in_array($order->status, self::ADMIN_CANCELLABLE, true);
    }

    /**
     * @return list<OrderStatus>
     */
    public function allowedAdminStatusUpdates(Order $order): array
    {
        return self::ADMIN_TRANSITIONS[$order->status->value] ?? [];
    }

    /**
     * @return list<OrderStatus>
     */
    public function allowedRestaurantStatusUpdates(Order $order): array
    {
        return self::RESTAURANT_TRANSITIONS[$order->status->value] ?? [];
    }

    public function canAdminReassignDeliveryPartner(Order $order): bool
    {
        return $order->status === OrderStatus::ReadyForPickup;
    }

    public function transition(
        Order $order,
        OrderStatus $toStatus,
        ?string $actorType = null,
        ?int $actorId = null,
        ?string $note = null,
        string $guard = 'restaurant'
    ): Order {
        $from = $order->status;
        $allowed = $guard === 'admin'
            ? (self::ADMIN_TRANSITIONS[$from->value] ?? [])
            : (self::RESTAURANT_TRANSITIONS[$from->value] ?? []);

        if (! in_array($toStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from {$from->label()} to {$toStatus->label()}.",
            ]);
        }

        return DB::transaction(function () use ($order, $from, $toStatus, $actorType, $actorId, $note) {
            $updates = ['status' => $toStatus];

            if ($toStatus === OrderStatus::Accepted) {
                $updates['accepted_at'] = now();
            }

            if ($toStatus === OrderStatus::Delivered) {
                $updates['delivered_at'] = now();
            }

            $order->update($updates);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => $from->value,
                'to_status' => $toStatus->value,
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'note' => $note,
            ]);

            if ($toStatus === OrderStatus::Accepted) {
                app(GstService::class)->applyGstToOrder($order->fresh());
                $this->commissionService->recordRestaurantEarning($order->fresh());
            }

            if ($toStatus === OrderStatus::Delivered) {
                $this->commissionService->recordDeliveryPartnerEarning($order->fresh());
            }

            $fresh = $order->fresh();
            app(RestaurantNotificationService::class)->handleOrderTransition($fresh, $from, $toStatus, $note);

            return $fresh;
        });
    }

    public function notifyRestaurantNewOrder(Order $order): void
    {
        app(RestaurantNotificationService::class)->newOrderRequest($order);
    }

    public static function generateOrderNumber(): string
    {
        return 'ON'.now()->format('ymd').strtoupper(substr(uniqid(), -6));
    }
}
