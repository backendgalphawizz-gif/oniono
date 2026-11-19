<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\DeliveryPartner;
use App\Models\DeliveryPartnerEarning;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantCommission;
use App\Models\RestaurantEarning;
use App\Models\Setting;
use App\Models\DeliveryPartnerCommission;

class CommissionService
{
    public function isEnabled(): bool
    {
        return (bool) Setting::getValue('commission_enabled', true);
    }

    public function masterRestaurantPercentage(): float
    {
        return (float) Setting::getValue('default_commission_percentage', 10);
    }

    public function masterDeliveryPartnerPercentage(): float
    {
        return (float) Setting::getValue('default_delivery_partner_commission_percentage', 15);
    }

    public function restaurantProfileCommission(Restaurant $restaurant): ?RestaurantCommission
    {
        if ($restaurant->relationLoaded('activeCommission')) {
            $override = $restaurant->getRelation('activeCommission');

            return $override && $override->is_active ? $override : null;
        }

        return RestaurantCommission::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    public function deliveryPartnerProfileCommission(DeliveryPartner $partner): ?DeliveryPartnerCommission
    {
        if ($partner->relationLoaded('activeCommission')) {
            $override = $partner->getRelation('activeCommission');

            return $override && $override->is_active ? $override : null;
        }

        return DeliveryPartnerCommission::query()
            ->where('delivery_partner_id', $partner->id)
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    /**
     * @return array{percentage: float, source: 'master'|'profile'}
     */
    public function resolveRestaurantCommission(Restaurant $restaurant): array
    {
        $override = $this->restaurantProfileCommission($restaurant);

        if ($override) {
            return [
                'percentage' => (float) $override->percentage,
                'source' => 'profile',
            ];
        }

        return [
            'percentage' => $this->masterRestaurantPercentage(),
            'source' => 'master',
        ];
    }

    public function resolveRestaurantPercentage(Restaurant $restaurant): float
    {
        return $this->resolveRestaurantCommission($restaurant)['percentage'];
    }

    /** @deprecated Use resolveRestaurantPercentage */
    public function resolvePercentage(Restaurant $restaurant): float
    {
        return $this->resolveRestaurantPercentage($restaurant);
    }

    /**
     * @return array{percentage: float, source: 'master'|'profile'}
     */
    public function resolveDeliveryPartnerCommission(DeliveryPartner $partner): array
    {
        $override = $this->deliveryPartnerProfileCommission($partner);

        if ($override) {
            return [
                'percentage' => (float) $override->percentage,
                'source' => 'profile',
            ];
        }

        return [
            'percentage' => $this->masterDeliveryPartnerPercentage(),
            'source' => 'master',
        ];
    }

    public function resolveDeliveryPartnerPercentage(DeliveryPartner $partner): float
    {
        return $this->resolveDeliveryPartnerCommission($partner)['percentage'];
    }

    public function applyRestaurantCommissionToOrder(Order $order): Order
    {
        if (! $this->isEnabled()) {
            return $order;
        }

        $restaurant = $order->restaurant;
        $percentage = $this->resolveRestaurantPercentage($restaurant);

        $commissionable = max(0, (float) $order->food_subtotal - (float) $order->discount_amount);
        $commissionAmount = round($commissionable * ($percentage / 100), 2);
        $restaurantEarning = round($commissionable - $commissionAmount, 2);

        $order->update([
            'commission_percentage_applied' => $percentage,
            'commissionable_amount' => $commissionable,
            'commission_amount' => $commissionAmount,
            'restaurant_earning_amount' => $restaurantEarning,
        ]);

        return $order->fresh();
    }

    /** @deprecated Use applyRestaurantCommissionToOrder */
    public function applyToOrder(Order $order): Order
    {
        return $this->applyRestaurantCommissionToOrder($order);
    }

    public function applyDeliveryPartnerCommissionToOrder(Order $order): Order
    {
        if (! $this->isEnabled()) {
            return $order;
        }

        $assignment = $order->deliveryAssignment;

        if (! $assignment?->deliveryPartner) {
            return $order;
        }

        $percentage = $this->resolveDeliveryPartnerPercentage($assignment->deliveryPartner);
        $deliveryFee = max(0, (float) $order->delivery_charge);
        $platformCut = round($deliveryFee * ($percentage / 100), 2);
        $partnerPayout = round($deliveryFee - $platformCut, 2);

        $order->update([
            'delivery_partner_commission_percentage_applied' => $percentage,
            'delivery_partner_commission_amount' => $platformCut,
            'delivery_partner_payout_amount' => $partnerPayout,
        ]);

        return $order->fresh();
    }

    public function recordRestaurantEarning(Order $order): ?RestaurantEarning
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $order = $this->applyRestaurantCommissionToOrder($order);

        return RestaurantEarning::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'restaurant_id' => $order->restaurant_id,
                'food_subtotal' => $order->food_subtotal,
                'discount_amount' => $order->discount_amount,
                'commissionable_amount' => $order->commissionable_amount,
                'commission_percentage' => $order->commission_percentage_applied,
                'commission_amount' => $order->commission_amount,
                'net_earning' => $order->restaurant_earning_amount,
                'status' => 'pending',
            ]
        );
    }

    /** @deprecated Use recordRestaurantEarning */
    public function recordEarning(Order $order): ?RestaurantEarning
    {
        return $this->recordRestaurantEarning($order);
    }

    public function recordDeliveryPartnerEarning(Order $order): ?DeliveryPartnerEarning
    {
        if (! $this->isEnabled() || $order->status !== OrderStatus::Delivered) {
            return null;
        }

        $order = $this->applyDeliveryPartnerCommissionToOrder($order);
        $assignment = $order->deliveryAssignment;

        if (! $assignment) {
            return null;
        }

        return DeliveryPartnerEarning::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'delivery_partner_id' => $assignment->delivery_partner_id,
                'amount' => $order->delivery_partner_payout_amount ?? 0,
                'status' => 'pending',
            ]
        );
    }

    public function recordOrderCommissions(Order $order): void
    {
        app(GstService::class)->applyGstToOrder($order->fresh());
        $this->recordRestaurantEarning($order->fresh());
        $this->recordDeliveryPartnerEarning($order->fresh());
    }
}
