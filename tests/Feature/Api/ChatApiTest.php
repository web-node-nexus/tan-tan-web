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

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_start_chat_send_message_and_offer(): void
    {
        [$listing, $seller] = $this->seedListing();
        $buyer = User::factory()->create(['phone' => '9000000001', 'name' => 'Buyer']);

        Sanctum::actingAs($buyer);

        $start = $this->postJson('/api/v1/conversations', [
            'listing_id' => $listing->id,
            'message' => 'Hi! Is this still available?',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $conversationId = $start->json('data.id');
        $this->assertNotNull($conversationId);

        $this->getJson('/api/v1/conversations')
            ->assertOk()
            ->assertJsonPath('data.0.id', $conversationId);

        $this->postJson("/api/v1/conversations/{$conversationId}/messages", [
            'body' => 'Can you do a better price?',
        ])
            ->assertCreated()
            ->assertJsonPath('data.body', 'Can you do a better price?');

        $this->postJson("/api/v1/conversations/{$conversationId}/offers", [
            'amount' => 8500,
        ])
            ->assertCreated()
            ->assertJsonPath('data.amount', 8500)
            ->assertJsonPath('data.status', 'pending');

        Sanctum::actingAs($seller);

        $this->getJson("/api/v1/conversations/{$conversationId}/messages")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_can_favourite_listing(): void
    {
        [$listing] = $this->seedListing();
        $user = User::factory()->create(['phone' => '9000000002']);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/listings/{$listing->id}/favourite")
            ->assertOk()
            ->assertJsonPath('data.is_favourited', true);

        $this->getJson('/api/v1/me/favourites')
            ->assertOk()
            ->assertJsonPath('data.0.id', $listing->id);
    }

    /**
     * @return array{0: Listing, 1: User}
     */
    private function seedListing(): array
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
        $seller = User::factory()->create(['phone' => '9000000003', 'name' => 'Seller']);
        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'city_id' => $city->id,
            'title' => 'Chat Demo Phone',
            'slug' => 'chat-demo-phone',
            'description' => 'Seeded listing for chat and favourite API coverage tests.',
            'price' => 9999,
            'condition' => 'used',
            'status' => 'active',
            'location_label' => 'Lucknow',
            'published_at' => now(),
        ]);

        return [$listing, $seller];
    }
}
