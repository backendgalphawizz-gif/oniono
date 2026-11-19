<?php

namespace Tests\Feature;

use App\Enums\RestaurantStatus;
use App\Models\FoodItem;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestaurantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_cannot_access_other_restaurant_menu_item(): void
    {
        $a = Restaurant::factory()->create(['status' => RestaurantStatus::Approved]);
        $b = Restaurant::factory()->create(['status' => RestaurantStatus::Approved]);

        $userA = RestaurantUser::query()->create([
            'restaurant_id' => $a->id,
            'name' => 'Owner A',
            'email' => 'a@test.com',
            'password' => Hash::make('password'),
        ]);

        $itemB = FoodItem::query()->create([
            'restaurant_id' => $b->id,
            'name' => 'Secret Item',
            'slug' => 'secret-item',
            'price' => 100,
        ]);

        $this->withoutMiddleware([\App\Http\Middleware\EnsureRestaurantApproved::class])
            ->actingAs($userA, 'restaurant')
            ->get(route('restaurant.menu.edit', $itemB))
            ->assertForbidden();
    }
}
