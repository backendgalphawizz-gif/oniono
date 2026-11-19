<?php

namespace Tests\Feature;

use App\Enums\RestaurantStatus;
use App\Models\Admin;
use App\Models\City;
use App\Models\Restaurant;
use App\Models\RestaurantDocument;
use App\Models\State;
use Database\Seeders\StateCitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RestaurantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->seed(StateCitySeeder::class);
    }

    public function test_documents_step_redirects_without_step_one_session(): void
    {
        $this->get(route('restaurant.register.documents'))
            ->assertRedirect(route('restaurant.register'))
            ->assertSessionHasErrors('register');
    }

    public function test_step_two_rejects_invalid_ifsc(): void
    {
        $this->verifyPhone('9876543210');

        $this->post(route('restaurant.register.step1'), $this->stepOnePayload())
            ->assertRedirect(route('restaurant.register.documents'));

        $pan = UploadedFile::fake()->create('pan.pdf', 200, 'application/pdf');

        $this->post(route('restaurant.register.submit'), array_merge($this->payoutPayload([
            'bank_ifsc' => 'INVALID',
        ]), [
            'documents' => ['pan_card' => $pan],
        ]))->assertSessionHasErrors('bank_ifsc');
    }

    public function test_step_one_rejects_unverified_phone(): void
    {
        $this->post(route('restaurant.register.step1'), $this->stepOnePayload())
            ->assertSessionHasErrors('phone');
    }

    public function test_successful_registration_with_pan_only(): void
    {
        $this->verifyPhone('9876543210');

        $this->post(route('restaurant.register.step1'), $this->stepOnePayload())
            ->assertRedirect(route('restaurant.register.documents'));

        $pan = UploadedFile::fake()->create('pan.pdf', 200, 'application/pdf');

        $this->post(route('restaurant.register.submit'), array_merge($this->payoutPayload(), [
            'documents' => ['pan_card' => $pan],
        ]))->assertRedirect(route('restaurant.login'));

        $restaurant = Restaurant::query()->where('email', 'newrest@example.com')->first();
        $this->assertNotNull($restaurant);
        $this->assertSame('919876543210', $restaurant->phone);
        $this->assertSame('HDFC0001234', $restaurant->bank_ifsc);
        $this->assertNotNull($restaurant->phone_verified_at);
        $this->assertSame(RestaurantStatus::Pending, $restaurant->status);

        $this->assertDatabaseHas('restaurant_documents', [
            'restaurant_id' => $restaurant->id,
            'type' => 'pan_card',
        ]);
    }

    public function test_admin_can_approve_when_only_pan_verified(): void
    {
        $restaurant = Restaurant::factory()->create([
            'status' => RestaurantStatus::Pending,
        ]);

        $document = RestaurantDocument::query()->create([
            'restaurant_id' => $restaurant->id,
            'type' => 'pan_card',
            'path' => 'restaurants/documents/test-pan.pdf',
            'verification_status' => 'verified',
        ]);

        $admin = Admin::query()->create([
            'name' => 'Admin',
            'login_id' => 'admin_test',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.restaurants.approve', $restaurant));

        $response->assertRedirect()->assertSessionHasNoErrors();

        $restaurant->refresh();
        $this->assertSame(RestaurantStatus::Approved, $restaurant->status);
        $this->assertNotNull($document->fresh());
    }

    private function verifyPhone(string $phone): void
    {
        config(['app.debug' => true]);

        $send = $this->postJson(route('restaurant.register.otp.send'), ['phone' => $phone]);
        $send->assertOk();

        $code = $send->json('debug_code');
        $this->assertNotEmpty($code);

        $this->postJson(route('restaurant.register.otp.verify'), [
            'phone' => $phone,
            'otp' => $code,
        ])->assertOk();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function stepOnePayload(array $overrides = []): array
    {
        $stateId = State::query()->where('name', 'Maharashtra')->value('id');
        $cityId = City::query()->where('state_id', $stateId)->where('name', 'Mumbai')->value('id');

        return array_merge([
            'name' => 'Test Kitchen',
            'owner_name' => 'Owner Name',
            'email' => 'newrest@example.com',
            'phone' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'address' => '123 Main Street',
            'state_id' => $stateId,
            'city_id' => $cityId,
            'pincode' => '400001',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payoutPayload(array $overrides = []): array
    {
        return array_merge([
            'bank_account_name' => 'Owner Name',
            'bank_name' => 'HDFC Bank',
            'bank_account_number' => '123456789012',
            'bank_ifsc' => 'HDFC0001234',
        ], $overrides);
    }
}
