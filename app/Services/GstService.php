<?php

namespace App\Services;

use App\Models\FoodItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Setting;

class GstService
{
    public function masterGstPercentage(): float
    {
        return (float) Setting::getValue('default_gst_percentage', 5);
    }

    /**
     * Effective GST % for a menu item, or null when GST does not apply.
     */
    public function resolveFoodGstPercentage(FoodItem $food, ?Restaurant $restaurant = null): ?float
    {
        if (! $food->is_gst_applicable) {
            return null;
        }

        if ($food->gst_percentage !== null) {
            return (float) $food->gst_percentage;
        }

        $restaurant ??= $food->restaurant;

        if ($restaurant && $restaurant->gst_enabled && $restaurant->default_gst_percentage !== null) {
            return (float) $restaurant->default_gst_percentage;
        }

        return $this->masterGstPercentage();
    }

    public function calculateGstOnAmount(float $amount, ?float $percentage): float
    {
        if ($percentage === null || $percentage <= 0) {
            return 0.0;
        }

        return round($amount * ($percentage / 100), 2);
    }

    /**
     * Snapshot GST on order line items and roll up to order tax fields.
     */
    public function applyGstToOrder(Order $order): Order
    {
        $order->loadMissing(['items.foodItem.restaurant', 'restaurant']);

        $foodGstTotal = 0.0;

        foreach ($order->items as $item) {
            $food = $item->food_item_id
                ? FoodItem::query()->find($item->food_item_id)
                : null;

            $percentage = null;
            if ($food) {
                $percentage = $this->resolveFoodGstPercentage($food, $order->restaurant);
            } elseif ($item->is_gst_applicable) {
                $percentage = $item->gst_percentage !== null
                    ? (float) $item->gst_percentage
                    : ($order->restaurant?->default_gst_percentage ?? $this->masterGstPercentage());
            }

            $gstAmount = $this->calculateGstOnAmount((float) $item->total_price, $percentage);

            $item->update([
                'is_gst_applicable' => $percentage !== null,
                'gst_percentage' => $percentage,
                'gst_amount' => $gstAmount,
            ]);

            $foodGstTotal += $gstAmount;
        }

        $order->update([
            'food_gst_amount' => $foodGstTotal,
            'tax_amount' => $foodGstTotal,
        ]);

        return $order->fresh();
    }
}
