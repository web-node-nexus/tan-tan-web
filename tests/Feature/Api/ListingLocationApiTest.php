<?php

namespace Tests\Feature\Api;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Listing;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ListingLocationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_radius_excludes_far_ads_and_distance_sorts_nearest_first(): void
    {
        [$category, $city, $user] = $this->placeBasics();

        Listing::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'city_id' => $city->id,
            'title' => 'Near Hazratganj phone',
            'slug' => 'near-phone',
            'description' => 'A phone listed inside Hazratganj for nearby buyers only.',
            'price' => 9000,
            'condition' => 'used',
            'status' => 'active',
            'latitude' => 26.851,
            'longitude' => 80.946,
            'published_at' => now()->subHour(),
        ]);

        Listing::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'city_id' => $city->id,
            'title' => 'Delhi sofa far away',
            'slug' => 'far-sofa',
            'description' => 'A sofa listed in Delhi, well outside a Lucknow radius.',
            'price' => 4000,
            'condition' => 'used',
            'status' => 'active',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'published_at' => now(),
        ]);

        $this->getJson('/api/v1/listings?lat=26.85&lng=80.946&radius_km=15')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Near Hazratganj phone');

        $this->getJson('/api/v1/listings?lat=26.85&lng=80.946&sort=distance')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Near Hazratganj phone')
            ->assertJsonPath('data.1.title', 'Delhi sofa far away');
    }

    public function test_area_filter_and_reverse_fallback_without_google(): void
    {
        [$category, $city, $user] = $this->placeBasics();
        $area = Area::query()->create([
            'city_id' => $city->id,
            'name' => 'Hazratganj',
            'latitude' => 26.8508,
            'longitude' => 80.9462,
        ]);

        Listing::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'city_id' => $city->id,
            'area_id' => $area->id,
            'title' => 'Hazratganj cycle',
            'slug' => 'hazratganj-cycle',
            'description' => 'City cycle parked near Hazratganj crossing for local pickup.',
            'price' => 2500,
            'condition' => 'used',
            'status' => 'active',
            'latitude' => 26.8508,
            'longitude' => 80.9462,
            'published_at' => now(),
        ]);

        config(['services.google.maps_key' => '']);

        $this->getJson('/api/v1/areas?city_id='.$city->id)
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Hazratganj');

        $this->getJson('/api/v1/listings?area_id='.$area->id)
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Hazratganj cycle');

        $this->getJson('/api/v1/places/reverse?lat=26.851&lng=80.947')
            ->assertOk()
            ->assertJsonPath('data.name', 'Hazratganj')
            ->assertJsonPath('data.city_id', $city->id);
    }

    public function test_google_reverse_saves_area(): void
    {
        [, $city] = $this->placeBasics();
        config(['services.google.maps_key' => 'test-key']);

        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'results' => [[
                    'place_id' => 'place-hazratganj',
                    'geometry' => ['location' => ['lat' => 26.8508, 'lng' => 80.9462]],
                    'address_components' => [
                        ['long_name' => 'Hazratganj', 'types' => ['sublocality_level_1', 'political']],
                        ['long_name' => 'Lucknow', 'types' => ['locality', 'political']],
                    ],
                ]],
            ]),
        ]);

        $this->getJson('/api/v1/places/reverse?lat=26.85&lng=80.95')
            ->assertOk()
            ->assertJsonPath('data.name', 'Hazratganj')
            ->assertJsonPath('data.city_id', $city->id);

        $this->assertDatabaseHas('areas', [
            'google_place_id' => 'place-hazratganj',
            'name' => 'Hazratganj',
            'city_id' => $city->id,
        ]);
    }

    /**
     * @return array{0: Category, 1: City, 2: User}
     */
    private function placeBasics(): array
    {
        $state = State::query()->create(['name' => 'Uttar Pradesh', 'code' => 'UP']);
        $city = City::query()->create([
            'state_id' => $state->id,
            'name' => 'Lucknow',
            'latitude' => 26.8467,
            'longitude' => 80.9462,
            'is_active' => true,
        ]);
        $category = Category::query()->create([
            'slug' => 'mobiles',
            'name_en' => 'Mobiles',
            'name_hi' => 'मोबाइल',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return [$category, $city, User::factory()->create()];
    }
}
