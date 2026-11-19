<?php

namespace Tests\Feature;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestaurantProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_restaurant_can_view_profile_management_page(): void
    {
        $user = $this->restaurantUser();

        $this->actingAs($user, 'restaurant')
            ->get(route('restaurant.profile.edit'))
            ->assertOk()
            ->assertSee('Profile management')
            ->assertSee('Opening hours')
            ->assertSee('Restaurant availability');
    }

    public function test_restaurant_can_update_profile_details_and_toggle_availability(): void
    {
        $user = $this->restaurantUser();
        $restaurant = $user->restaurant;

        $this->actingAs($user, 'restaurant')
            ->put(route('restaurant.profile.update'), [
                'name' => 'Updated Bistro',
                'owner_name' => 'Updated Owner',
                'phone' => '9876543210',
                'description' => 'New description',
                'address' => '42 Test Lane',
                'city' => 'Pune',
                'state' => 'MH',
                'pincode' => '411001',
            ])
            ->assertRedirect(route('restaurant.profile.edit').'#profile-details');

        $restaurant->refresh();
        $this->assertSame('Updated Bistro', $restaurant->name);

        $this->actingAs($user, 'restaurant')
            ->put(route('restaurant.timings.update'), [
                'is_open' => '0',
                'from_availability' => '1',
            ])
            ->assertRedirect(route('restaurant.profile.edit').'#availability');

        $this->assertFalse($restaurant->fresh()->is_open);
    }

    private function restaurantUser(): RestaurantUser
    {
        $restaurant = Restaurant::factory()->create([
            'status' => RestaurantStatus::Approved,
            'is_open' => true,
        ]);

        return RestaurantUser::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Owner',
            'email' => 'profile@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
    }
}
