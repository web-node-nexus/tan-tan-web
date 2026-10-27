<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Support\ProductAttributes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTradeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_product_category_stores_its_own_fields(): void
    {
        foreach (ProductAttributes::examples() as $slug => $attributes) {
            $category = Category::query()->create([
                'slug' => $slug,
                'name_en' => $slug,
                'name_hi' => $slug,
                'kind' => 'product',
                'icon' => 'Tag',
                'tint' => '#fff',
                'sort_order' => 1,
                'is_active' => true,
            ]);
            Sanctum::actingAs(User::factory()->create());

            $created = $this->postJson('/api/v1/listings', [
                'title' => 'For sale '.$slug,
                'description' => 'A local item with a clear price, honest condition and pickup in the city.',
                'price' => 4999,
                'category_id' => $category->id,
                'condition' => 'used',
                'is_negotiable' => false,
                'attributes' => $attributes,
            ])->assertCreated()
                ->assertJsonPath('data.is_negotiable', false);

            foreach ($attributes as $key => $value) {
                $created->assertJsonPath('data.attributes.'.$key, $value);
            }

            $this->postJson('/api/v1/listings', [
                'title' => 'Incomplete '.$slug,
                'description' => 'This description is long enough but the product fields are missing.',
                'price' => 4999,
                'category_id' => $category->id,
                'condition' => 'used',
            ])->assertStatus(422);
        }
    }

    public function test_accepted_offer_reserves_the_listing_at_the_agreed_price(): void
    {
        $category = Category::query()->create([
            'slug' => 'mobiles',
            'name_en' => 'Mobiles',
            'name_hi' => 'मोबाइल',
            'kind' => 'product',
            'icon' => 'DeviceMobile',
            'tint' => '#fff',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $seller = User::factory()->create();
        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Phone open to offers',
            'slug' => 'phone-open-to-offers',
            'description' => 'Used phone with bill. Price is negotiable for a serious local buyer.',
            'price' => 10000,
            'condition' => 'used',
            'is_negotiable' => true,
            'status' => 'active',
            'attributes' => ['brand' => 'samsung', 'storage' => '128', 'bill' => true],
            'published_at' => now(),
        ]);

        $buyer = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($buyer);
        $chat = $this->postJson('/api/v1/conversations', [
            'listing_id' => $listing->id,
            'message' => 'Is the price negotiable today?',
        ])->assertCreated();

        $offer = $this->postJson('/api/v1/conversations/'.$chat->json('data.id').'/offers', [
            'amount' => 8500,
        ])->assertCreated();

        Sanctum::actingAs($other);
        $otherChat = $this->postJson('/api/v1/conversations', [
            'listing_id' => $listing->id,
            'message' => 'I can pick it up this evening.',
        ])->assertCreated();
        $lower = $this->postJson('/api/v1/conversations/'.$otherChat->json('data.id').'/offers', [
            'amount' => 7000,
        ])->assertCreated();

        Sanctum::actingAs($seller);
        $this->postJson('/api/v1/offers/'.$offer->json('data.id').'/respond', ['status' => 'accepted'])
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'price' => 8500,
        ]);
        $this->assertNotNull($listing->fresh()->reserved_at);
        $this->assertDatabaseHas('offers', [
            'id' => $lower->json('data.id'),
            'status' => 'rejected',
        ]);

        Sanctum::actingAs($buyer);
        $this->postJson('/api/v1/conversations/'.$chat->json('data.id').'/offers', [
            'amount' => 8000,
        ])->assertStatus(422);
    }
}
