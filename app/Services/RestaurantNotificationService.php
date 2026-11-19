<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PanelNotification;
use App\Models\Restaurant;
use App\Models\Settlement;

class RestaurantNotificationService
{
    public function notify(
        int $restaurantId,
        string $type,
        string $title,
        ?string $body = null,
        array $data = [],
    ): PanelNotification {
        return PanelNotification::query()->create([
            'notifiable_type' => 'restaurant',
            'notifiable_id' => $restaurantId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    public function newOrderRequest(Order $order): void
    {
        if ($order->status !== OrderStatus::Pending) {
            return;
        }

        $this->notify(
            $order->restaurant_id,
            'order_new',
            'New order #'.$order->order_number,
            'A customer placed a new order. Accept or reject from Orders.',
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'url' => route('restaurant.orders.show', $order),
            ]
        );
    }

    public function orderCancelled(Order $order, OrderStatus $fromStatus, ?string $note = null): void
    {
        if (! in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Rejected], true)) {
            return;
        }

        $label = $order->status === OrderStatus::Rejected ? 'rejected' : 'cancelled';
        $body = $note ?: $order->cancellation_reason ?: 'This order is no longer active.';

        $this->notify(
            $order->restaurant_id,
            'order_cancelled',
            'Order #'.$order->order_number.' '.$label,
            $body,
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'from_status' => $fromStatus->value,
                'url' => route('restaurant.orders.show', $order),
            ]
        );
    }

    public function settlementUpdate(Settlement $settlement): void
    {
        $this->notify(
            $settlement->restaurant_id,
            'settlement',
            'Settlement '.$settlement->settlement_number,
            'Payout of '.money_inr($settlement->amount).' — status: '.ucfirst(str_replace('_', ' ', $settlement->status->value ?? (string) $settlement->status)).'.',
            [
                'settlement_id' => $settlement->id,
                'settlement_number' => $settlement->settlement_number,
                'amount' => (float) $settlement->amount,
                'url' => route('restaurant.settlements.show', $settlement),
            ]
        );
    }

    public function platformAnnouncement(string $title, string $body, array $data = []): void
    {
        Restaurant::query()->pluck('id')->each(function (int $restaurantId) use ($title, $body, $data) {
            $this->notify(
                $restaurantId,
                'announcement',
                $title,
                $body,
                array_merge(['url' => route('restaurant.notifications.index')], $data)
            );
        });
    }

    public function handleOrderTransition(Order $order, OrderStatus $from, OrderStatus $to, ?string $note = null): void
    {
        if (in_array($to, [OrderStatus::Cancelled, OrderStatus::Rejected], true)) {
            $this->orderCancelled($order, $from, $note);
        }
    }

    public function typeMeta(string $type): array
    {
        return config('restaurant_notifications.types.'.$type, [
            'label' => ucfirst(str_replace('_', ' ', $type)),
            'icon' => 'bell',
            'badge' => 'secondary',
        ]);
    }
}
