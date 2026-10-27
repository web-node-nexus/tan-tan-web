<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_active_listings(): void
    {
        $this->seedMarketplaceBasics();

        $this->getJson('/api/v1/listings')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.title', 'Demo Phone');
    }

    public function test_auth_user_can_create_listing(): void
    {
        [$category, $city] = $this->seedMarketplaceBasics(withListing: false);
        $user = User::factory()->create(['phone' => '9123456780']);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/listings', [
            'title' => 'Redmi Note 12 Pro 256GB',
            'description' => 'Excellent condition phone with bill and box. Local buyers only please.',
            'price' => 15999,
            'category_id' => $category->id,
            'city_id' => $city->id,
            'condition' => 'like_new',
            'attributes' => ['brand' => 'xiaomi', 'storage' => '256', 'bill' => true],
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Redmi Note 12 Pro 256GB')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('listings', [
            'user_id' => $user->id,
            'title' => 'Redmi Note 12 Pro 256GB',
        ]);
    }

    /**
     * @return array{0: Category, 1: City}
     */
    private function seedMarketplaceBasics(bool $withListing = true): array
    {
        $state = State::query()->create(['name' => 'Uttar Pradesh', 'code' => 'UP']);
        $city = City::query()->create([
            'state_id' => $state->id,
            'name' => 'Lucknow',
            'latitude' => 26.8,
            'longitude' => 80.9,
            'is_active' => true,
        ]);
        $category = Category::query()->create([
            'slug' => 'mobiles',
            'name_en' => 'Mobiles',
            'name_hi' => 'मोबाइल',
            'icon' => 'DeviceMobile',
            'tint' => '#E8F1FF',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        if ($withListing) {
            $user = User::factory()->create();
            Listing::query()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'city_id' => $city->id,
                'title' => 'Demo Phone',
                'slug' => 'demo-phone',
                'description' => 'A seeded listing used for API smoke tests and browse screens.',
                'price' => 9999,
                'condition' => 'used',
                'status' => 'active',
                'location_label' => 'Lucknow',
                'published_at' => now(),
            ]);
        }

        return [$category, $city];
    }
}
