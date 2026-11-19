<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Restaurant;
use App\Models\RestaurantEarning;
use App\Services\SettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettlementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_settlement_marks_earnings_as_settled_once(): void
    {
        $restaurant = Restaurant::factory()->create();
        $admin = Admin::query()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        $earning = RestaurantEarning::query()->create([
            'restaurant_id' => $restaurant->id,
            'order_id' => \App\Models\Order::factory()->create(['restaurant_id' => $restaurant->id])->id,
            'food_subtotal' => 1000,
            'commissionable_amount' => 900,
            'commission_percentage' => 10,
            'commission_amount' => 90,
            'net_earning' => 810,
            'status' => 'pending',
        ]);

        $settlement = app(SettlementService::class)->createSettlement(
            $restaurant,
            [$earning->id],
            810,
            $admin->id,
            ['payment_mode' => 'bank']
        );

        $this->assertDatabaseHas('settlement_items', ['restaurant_earning_id' => $earning->id]);
        $this->assertEquals('settled', $earning->fresh()->status);
        $this->assertNotNull($settlement->settlement_number);
    }
}
