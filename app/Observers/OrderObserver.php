<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->status !== OrderStatus::Pending) {
            return;
        }

        app(OrderService::class)->notifyRestaurantNewOrder($order);
    }
}
