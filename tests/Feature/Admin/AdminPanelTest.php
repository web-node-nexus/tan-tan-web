<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Listings\Pages\ListListings;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Category;
use App\Models\City;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Report;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Listing $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Asha Admin']);
        $this->admin->assignRole(Role::findOrCreate('Super Admin'));

        $state = State::query()->create(['name' => 'Maharashtra', 'code' => 'MH']);
        $city = City::query()->create(['state_id' => $state->id, 'name' => 'Pune']);
        $category = Category::query()->create(['slug' => 'mobiles', 'name_en' => 'Mobiles', 'name_hi' => 'मोबाइल']);
        $seller = User::factory()->create(['name' => 'Ravi Seller', 'phone' => '9000000010']);

        $this->listing = Listing::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'city_id' => $city->id,
            'title' => 'iPhone 13 128GB',
            'description' => 'Single owner, bill available.',
            'price' => 42000,
            'condition' => 'used',
            'status' => 'pending',
        ]);

        $buyer = User::factory()->create(['phone' => '9000000011']);
        Report::query()->create(['reporter_id' => $buyer->id, 'listing_id' => $this->listing->id, 'reason' => 'fraud', 'status' => 'open']);
        Conversation::query()->create(['listing_id' => $this->listing->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id, 'last_message_at' => now()]);
    }

    public function test_every_admin_page_renders(): void
    {
        $this->actingAs($this->admin);

        $conversation = Conversation::query()->first();
        $report = Report::query()->first();

        $pages = [
            '/admin',
            '/admin?filters[period]=365',
            '/admin/listings',
            "/admin/listings/{$this->listing->id}",
            "/admin/listings/{$this->listing->id}/edit",
            '/admin/users',
            '/admin/users/create',
            "/admin/users/{$this->listing->user_id}",
            "/admin/users/{$this->listing->user_id}/edit",
            '/admin/reports',
            "/admin/reports/{$report->id}",
            '/admin/reviews',
            '/admin/conversations',
            "/admin/conversations/{$conversation->id}",
            '/admin/payments',
            '/admin/promotions',
            '/admin/packages',
            '/admin/categories',
            '/admin/banners',
            '/admin/cities',
            '/admin/states',
        ];

        $failed = [];
        foreach ($pages as $page) {
            $response = $this->get($page);
            if ($response->status() !== 200) {
                $failed[$page] = $response->exception ? $response->exception::class.': '.$response->exception->getMessage().' @ '.$response->exception->getFile().':'.$response->exception->getLine() : $response->status().' '.substr(strip_tags((string) $response->getContent()), 0, 300);
            }
        }

        $this->assertSame([], $failed);
    }

    public function test_non_staff_cannot_open_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_moderator_can_approve_listing_from_table(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListListings::class)
            ->callTableAction('approve', $this->listing)
            ->assertHasNoTableActionErrors();

        $this->listing->refresh();
        $this->assertSame('active', $this->listing->status);
        $this->assertNotNull($this->listing->published_at);
        $this->assertNotNull($this->listing->moderated_at);
    }

    public function test_rejecting_listing_stores_reason(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListListings::class)
            ->callTableAction('reject', $this->listing, data: ['note' => 'Blurry photos'])
            ->assertHasNoTableActionErrors();

        $this->assertSame('rejected', $this->listing->fresh()->status);
        $this->assertSame('Blurry photos', $this->listing->fresh()->moderation_note);
    }

    public function test_resolving_report_can_block_listing(): void
    {
        $this->actingAs($this->admin);
        $report = Report::query()->first();

        Livewire::test(ListReports::class)
            ->callTableAction('resolve', $report, data: ['note' => 'Confirmed scam', 'block_listing' => true])
            ->assertHasNoTableActionErrors();

        $report->refresh();
        $this->assertSame('resolved', $report->status);
        $this->assertSame($this->admin->id, $report->resolved_by);
        $this->assertSame('blocked', $this->listing->fresh()->status);
    }

    public function test_suspending_user_revokes_tokens_and_blocks_login(): void
    {
        $this->actingAs($this->admin);
        $seller = $this->listing->user;
        $this->listing->update(['status' => 'active']);
        $seller->createToken('mobile');

        Livewire::test(ListUsers::class)
            ->callTableAction('ban', $seller, data: ['reason' => 'Repeated scam reports', 'hide_listings' => true])
            ->assertHasNoTableActionErrors();

        $seller->refresh();
        $this->assertTrue($seller->isBanned());
        $this->assertSame(0, $seller->tokens()->count());
        $this->assertSame('blocked', $this->listing->fresh()->status);

        Livewire::test(ListUsers::class)
            ->callTableAction('unban', $seller, data: ['restore_listings' => true])
            ->assertHasNoTableActionErrors();

        $this->assertFalse($seller->fresh()->isBanned());
        $this->assertSame('active', $this->listing->fresh()->status);
    }
}
