<?php

namespace Tests\Feature\Api;

use App\Filament\Support\ListingModeration;
use App\Models\Category;
use App\Models\Listing;
use App\Models\SavedSearch;
use App\Models\User;
use App\Models\UserAlert;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogSearchAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_tree_and_typo_search_rank_the_title_match_first(): void
    {
        Category::query()->create([
            'slug' => 'mobiles',
            'name_en' => 'Mobiles',
            'name_hi' => 'मोबाइल',
            'kind' => 'product',
            'icon' => 'DeviceMobile',
            'tint' => '#E8F1FF',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $this->seed(CatalogSeeder::class);
        $category = Category::query()->where('slug', 'mobile-phones')->firstOrFail();
        $seller = User::factory()->create();

        Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Old box with a charger',
            'slug' => 'old-box',
            'description' => 'Someone mentioned an iphone in the description only, not the title.',
            'price' => 500,
            'condition' => 'used',
            'status' => 'active',
            'attributes' => ['brand' => 'Apple', 'model' => 'iPhone 13', 'variant' => '128 GB', 'bill' => true],
            'published_at' => now(),
        ]);
        Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'iPhone 13 blue',
            'slug' => 'iphone-13-blue',
            'description' => 'Phone with bill and box, local pickup.',
            'price' => 38000,
            'condition' => 'used',
            'status' => 'active',
            'attributes' => ['brand' => 'Apple', 'model' => 'iPhone 13', 'variant' => '128 GB', 'bill' => true],
            'published_at' => now()->subDay(),
        ]);

        $tree = $this->getJson('/api/v1/catalog')->assertOk()->json('data.mobiles');
        $apple = collect($tree)->firstWhere('name', 'Apple');
        $this->assertNotNull($apple);
        $this->assertSame('iPhone 13', $apple['children'][0]['name']);

        $this->getJson('/api/v1/listings?q=iphon&sort=relevance&brand=Apple&model=iPhone%2013')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'iPhone 13 blue');
    }

    public function test_saved_search_creates_an_alert_when_a_matching_ad_is_posted(): void
    {
        $category = Category::query()->create([
            'slug' => 'mobiles',
            'name_en' => 'Mobiles',
            'name_hi' => 'मोबाइल',
            'kind' => 'product',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $buyer = User::factory()->create(['locale' => 'en']);
        SavedSearch::query()->create([
            'user_id' => $buyer->id,
            'name' => 'iPhone under 40k',
            'filters' => ['q' => 'iphone', 'max_price' => 40000],
            'notify' => true,
        ]);

        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/v1/listings', [
            'title' => 'iPhone 13 for sale',
            'description' => 'Single owner phone with bill. Local buyers can pick it up today.',
            'price' => 35000,
            'category_id' => $category->id,
            'condition' => 'used',
            'attributes' => ['brand' => 'apple', 'storage' => '128', 'bill' => true],
        ])->assertCreated();

        $posted = Listing::query()->latest('id')->firstOrFail();
        $this->assertSame('pending', $posted->status);
        ListingModeration::applyStatus($posted, 'active');

        $this->assertDatabaseHas('user_alerts', [
            'user_id' => $buyer->id,
            'title' => 'New ad for your search',
        ]);

        Sanctum::actingAs($buyer);
        $this->getJson('/api/v1/me/alerts')
            ->assertOk()
            ->assertJsonPath('data.unread', 1)
            ->assertJsonPath('data.alerts.0.listing_id', Listing::query()->value('id'));

        $this->postJson('/api/v1/me/alerts/read')->assertOk();
        $this->assertNotNull(UserAlert::query()->value('read_at'));
    }
}
