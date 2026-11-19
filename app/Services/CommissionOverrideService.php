<?php

namespace App\Services;

use App\Models\DeliveryPartner;
use App\Models\DeliveryPartnerCommission;
use App\Models\Restaurant;
use App\Models\RestaurantCommission;
use Illuminate\Http\Request;

class CommissionOverrideService
{
    public function clearRestaurant(Restaurant $restaurant): void
    {
        RestaurantCommission::query()
            ->where('restaurant_id', $restaurant->id)
            ->update(['is_active' => false]);
    }

    public function setRestaurant(Restaurant $restaurant, float $percentage, ?int $adminId = null): void
    {
        $this->clearRestaurant($restaurant);

        RestaurantCommission::query()->create([
            'restaurant_id' => $restaurant->id,
            'percentage' => $percentage,
            'is_active' => true,
            'created_by_admin_id' => $adminId ?? auth('admin')->id(),
        ]);
    }

    public function applyRestaurantFromRequest(Request $request, Restaurant $restaurant): void
    {
        if ($request->boolean('clear_override')) {
            $this->clearRestaurant($restaurant);

            return;
        }

        if ($request->has('commission_use_master')) {
            if ($request->boolean('commission_use_master')) {
                $this->clearRestaurant($restaurant);

                return;
            }

            if ($request->filled('commission_percentage')) {
                $this->setRestaurant($restaurant, (float) $request->input('commission_percentage'));
            }

            return;
        }

        if ($request->filled('percentage')) {
            $this->setRestaurant($restaurant, (float) $request->input('percentage'));
        }
    }

    public function clearDeliveryPartner(DeliveryPartner $partner): void
    {
        DeliveryPartnerCommission::query()
            ->where('delivery_partner_id', $partner->id)
            ->update(['is_active' => false]);
    }

    public function setDeliveryPartner(DeliveryPartner $partner, float $percentage, ?int $adminId = null): void
    {
        $this->clearDeliveryPartner($partner);

        DeliveryPartnerCommission::query()->create([
            'delivery_partner_id' => $partner->id,
            'percentage' => $percentage,
            'is_active' => true,
            'created_by_admin_id' => $adminId ?? auth('admin')->id(),
        ]);
    }

    public function applyDeliveryPartnerFromRequest(Request $request, DeliveryPartner $partner): void
    {
        if ($request->boolean('clear_override')) {
            $this->clearDeliveryPartner($partner);

            return;
        }

        if ($request->has('commission_use_master')) {
            if ($request->boolean('commission_use_master')) {
                $this->clearDeliveryPartner($partner);

                return;
            }

            if ($request->filled('commission_percentage')) {
                $this->setDeliveryPartner($partner, (float) $request->input('commission_percentage'));
            }

            return;
        }

        if ($request->filled('percentage')) {
            $this->setDeliveryPartner($partner, (float) $request->input('percentage'));
        }
    }
}
