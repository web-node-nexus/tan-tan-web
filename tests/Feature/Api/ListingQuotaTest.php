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

class ListingQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_two_listings_are_free_third_requires_payment(): void
    {
        [$category, $city] = $this->seedBasics();
        $user = User::factory()->create(['phone' => '9888777666', 'email' => 'quota@tantan.test']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me/listing-quota')
            ->assertOk()
            ->assertJsonPath('data.free_limit', 2)
            ->assertJsonPath('data.free_remaining', 2)
            ->assertJsonPath('data.extra_ad_fee', 9)
            ->assertJsonPath('data.requires_payment', false);

        foreach ([1, 2] as $n) {
            $this->postJson('/api/v1/listings', $this->payload($category->id, $city->id, "Free Ad Number {$n} Title"))
                ->assertCreated()
                ->assertJsonPath('data.posting_fee', 0);
        }

        $this->getJson('/api/v1/me/listing-quota')
            ->assertOk()
            ->assertJsonPath('data.free_remaining', 0)
            ->assertJsonPath('data.requires_payment', true);

        $this->postJson('/api/v1/listings', $this->payload($category->id, $city->id, 'Paid Ad Needs Credit Now'))
            ->assertStatus(422);

        $pay = $this->postJson('/api/v1/me/listing-quota/purchase')
            ->assertCreated()
            ->assertJsonPath('data.amount', 9);

        $paymentId = $pay->json('data.payment_id');

        $this->postJson('/api/v1/listings', array_merge(
            $this->payload($category->id, $city->id, 'Third Ad After Paying Nine'),
            ['payment_id' => $paymentId],
        ))
            ->assertCreated()
            ->assertJsonPath('data.posting_fee', 9);

        $this->assertDatabaseHas('listing_payments', [
            'id' => $paymentId,
            'status' => 'consumed',
        ]);
    }

    /**
     * @return array{0: Category, 1: City}
     */
    private function seedBasics(): array
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

        return [$category, $city];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(int $categoryId, int $cityId, string $title): array
    {
        return [
            'title' => $title,
            'description' => 'Quota test listing description with enough characters for validation rules.',
            'price' => 4999,
            'category_id' => $categoryId,
            'city_id' => $cityId,
            'condition' => 'used',
            'attributes' => ['brand' => 'xiaomi', 'storage' => '128', 'bill' => true],
        ];
    }
}
