<?php

namespace Tests\Feature\Api;

use App\Filament\Support\ListingModeration;
use App\Models\Category;
use App\Models\Listing;
use App\Models\SellerFollow;
use App\Models\User;
use App\Models\UserAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AudienceAlertApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_followers_are_alerted_when_a_listing_goes_live(): void
    {
        $seller = User::factory()->create();
        $follower = User::factory()->create();
        SellerFollow::query()->create(['follower_id' => $follower->id, 'seller_id' => $seller->id]);
        $listing = $this->listing($seller, 'pending');

        ListingModeration::applyStatus($listing, 'active');

        $this->assertDatabaseHas('user_alerts', [
            'user_id' => $follower->id,
            'listing_id' => $listing->id,
            'kind' => 'follow',
        ]);
    }

    public function test_a_price_drop_alerts_someone_who_already_viewed_the_ad(): void
    {
        $seller = User::factory()->create();
        $viewer = User::factory()->create();
        $listing = $this->listing($seller, 'active');

        Sanctum::actingAs($viewer);
        $this->getJson('/api/v1/listings/'.$listing->id)->assertOk();

        Sanctum::actingAs($seller);
        $this->putJson('/api/v1/listings/'.$listing->id, ['price' => 250])
            ->assertOk()
            ->assertJsonPath('data.price_dropped', true);

        $alert = UserAlert::query()->where('user_id', $viewer->id)->where('kind', 'price_drop')->first();
        $this->assertNotNull($alert);
        $this->assertSame($listing->id, $alert->listing_id);
    }

    private function listing(User $seller, string $status): Listing
    {
        $category = Category::query()->create([
            'slug' => 'books-'.uniqid(),
            'name_en' => 'Books',
            'name_hi' => 'किताबें',
            'kind' => 'product',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Used book bundle',
            'slug' => 'used-book-'.uniqid(),
            'description' => 'Three novels in good condition. Pickup only, no advance payment.',
            'price' => 400,
            'condition' => 'used',
            'status' => $status,
            'is_featured' => false,
            'published_at' => $status === 'active' ? now() : null,
        ]);
    }
}
