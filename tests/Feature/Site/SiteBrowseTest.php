<?php

namespace Tests\Feature\Site;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteBrowseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_every_public_page_renders(): void
    {
        foreach ([
            '/',
            '/browse',
            '/about',
            '/how-it-works',
            '/safety-tips',
            '/packages',
            '/faq',
            '/contact',
            '/terms',
            '/privacy',
            '/delete-account',
            '/download',
            '/categories',
            '/for-sellers',
            '/for-buyers',
            '/sell',
            '/trust',
            '/work',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_browse_filters_by_kind_and_ad_page_renders(): void
    {
        $sales = Category::query()->where('slug', 'job-sales')->firstOrFail();
        $seller = User::factory()->create(['name' => 'Demo Employer']);

        $job = Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $sales->id,
            'title' => 'Store helper job',
            'slug' => 'store-helper-job',
            'description' => 'Full time shop helper in the neighbourhood. Salary paid monthly. No registration fee.',
            'price' => 14000,
            'condition' => 'new',
            'status' => 'active',
            'published_at' => now(),
        ]);

        $productCategory = Category::query()->create([
            'slug' => 'furniture-test',
            'kind' => 'product',
            'name_en' => 'Furniture',
            'name_hi' => 'फर्नीचर',
            'is_active' => true,
        ]);

        Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $productCategory->id,
            'title' => 'Beige study table',
            'slug' => 'beige-study-table',
            'description' => 'Solid wood study table with one drawer. Buyer arranges pickup inside the city.',
            'price' => 4500,
            'condition' => 'used',
            'status' => 'active',
            'published_at' => now(),
        ]);

        $this->get('/browse?kind=job')
            ->assertOk()
            ->assertSee('Store helper job')
            ->assertDontSee('Beige study table');

        $this->get('/browse?kind=product')
            ->assertOk()
            ->assertSee('Beige study table')
            ->assertDontSee('Store helper job');

        $this->get('/ads/'.$job->slug)
            ->assertOk()
            ->assertSee('Store helper job')
            ->assertSee('Application')
            ->assertSee('Applied')
            ->assertSee('Demo Employer');

        $this->get('/')
            ->assertOk()
            ->assertSee('Store helper job')
            ->assertSee('Fresh ads, jobs and services');
    }
}
