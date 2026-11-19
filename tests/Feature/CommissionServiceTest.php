<?php

namespace Tests\Feature;

use App\Enums\RestaurantStatus;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantCommission;
use App\Models\Setting;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_snapshot_uses_global_default(): void
    {
        Setting::setValue('default_commission_percentage', 10, 'decimal', 'commission');

        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Approved]);
        $order = Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'food_subtotal' => 1000,
            'discount_amount' => 100,
        ]);

        $order = app(CommissionService::class)->applyToOrder($order);

        $this->assertEquals(10.0, (float) $order->commission_percentage_applied);
        $this->assertEquals(90.0, (float) $order->commission_amount);
        $this->assertEquals(810.0, (float) $order->restaurant_earning_amount);
    }

    public function test_commission_uses_profile_rate_when_admin_set_on_restaurant(): void
    {
        Setting::setValue('default_commission_percentage', 10, 'decimal', 'commission');

        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Approved]);
        RestaurantCommission::query()->create([
            'restaurant_id' => $restaurant->id,
            'percentage' => 20,
            'is_active' => true,
        ]);

        $order = Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'food_subtotal' => 1000,
            'discount_amount' => 0,
        ]);

        $service = app(CommissionService::class);
        $resolved = $service->resolveRestaurantCommission($restaurant);

        $this->assertSame('profile', $resolved['source']);
        $this->assertEquals(20.0, $resolved['percentage']);

        $order = $service->applyToOrder($order);

        $this->assertEquals(20.0, (float) $order->commission_percentage_applied);
        $this->assertEquals(200.0, (float) $order->commission_amount);
    }

    public function test_commission_falls_back_to_master_after_profile_override_cleared(): void
    {
        Setting::setValue('default_commission_percentage', 12, 'decimal', 'commission');

        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::Approved]);
        $row = RestaurantCommission::query()->create([
            'restaurant_id' => $restaurant->id,
            'percentage' => 25,
            'is_active' => true,
        ]);
        $row->update(['is_active' => false]);

        $resolved = app(CommissionService::class)->resolveRestaurantCommission($restaurant);

        $this->assertSame('master', $resolved['source']);
        $this->assertEquals(12.0, $resolved['percentage']);
    }
}
