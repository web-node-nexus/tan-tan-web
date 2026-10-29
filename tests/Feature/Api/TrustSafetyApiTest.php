<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrustSafetyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_and_id_badges_are_separate(): void
    {
        $seller = User::factory()->create([
            'phone_verified_at' => now(),
            'is_verified' => false,
        ]);
        $category = $this->category();
        $listing = $this->listing($seller, $category, 'active');

        $this->getJson('/api/v1/sellers/'.$seller->id)
            ->assertOk()
            ->assertJsonPath('data.phone_verified', true)
            ->assertJsonPath('data.is_verified', false);

        $this->getJson('/api/v1/listings/'.$listing->id)
            ->assertOk()
            ->assertJsonPath('data.seller.phone_verified', true)
            ->assertJsonPath('data.seller.is_verified', false)
            ->assertJsonPath('data.can_review', false);
    }

    public function test_distinct_reports_auto_hide_the_ad_once(): void
    {
        config(['services.tantan.report_hide_threshold' => 3]);
        $seller = User::factory()->create();
        $listing = $this->listing($seller, $this->category(), 'active');
        $first = User::factory()->create();

        Sanctum::actingAs($first);
        $this->postJson('/api/v1/reports', ['listing_id' => $listing->id, 'reason' => 'fraud'])
            ->assertCreated()
            ->assertJsonPath('data.hidden', false);
        $this->postJson('/api/v1/reports', ['listing_id' => $listing->id, 'reason' => 'spam'])
            ->assertOk()
            ->assertJsonPath('data.already_reported', true)
            ->assertJsonPath('data.report_count', 1);

        foreach ([2, 3] as $n) {
            Sanctum::actingAs(User::factory()->create());
            $this->postJson('/api/v1/reports', ['listing_id' => $listing->id, 'reason' => 'fraud'])
                ->assertCreated()
                ->assertJsonPath('data.hidden', $n === 3);
        }

        $this->assertSame('blocked', $listing->fresh()->status);
        $this->assertSame(3, $listing->reports()->count());
    }

    public function test_rating_requires_a_completed_deal_or_chat(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->listing($seller, $this->category(), 'active');
        Sanctum::actingAs($buyer);

        $this->postJson('/api/v1/reviews', [
            'reviewee_id' => $seller->id,
            'listing_id' => $listing->id,
            'rating' => 5,
        ])->assertStatus(422)
            ->assertJsonPath('errors.listing_id.0', __('api.reviews.deal_required'));

        $listing->forceFill(['status' => 'sold', 'sold_at' => now()])->save();
        $conversation = Conversation::query()->create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'last_message_at' => now(),
        ]);
        Message::query()->create(['conversation_id' => $conversation->id, 'sender_id' => $buyer->id, 'body' => 'I will pick it up today.']);
        Message::query()->create(['conversation_id' => $conversation->id, 'sender_id' => $seller->id, 'body' => 'Sold, see you at the metro station.']);

        $this->postJson('/api/v1/reviews', [
            'reviewee_id' => $seller->id,
            'listing_id' => $listing->id,
            'rating' => 5,
            'comment' => 'Paid only after checking the item.',
        ])->assertCreated();

        $this->assertSame(1, $seller->fresh()->rating_count);
    }

    private function category(): Category
    {
        return Category::query()->create([
            'slug' => 'books-'.uniqid(),
            'name_en' => 'Books',
            'name_hi' => 'किताबें',
            'kind' => 'product',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function listing(User $seller, Category $category, string $status): Listing
    {
        return Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Used book bundle',
            'slug' => 'used-book-bundle-'.uniqid(),
            'description' => 'Three novels in good condition. Pickup only, no advance payment.',
            'price' => 400,
            'condition' => 'used',
            'status' => $status,
            'published_at' => $status === 'active' ? now() : null,
        ]);
    }
}
