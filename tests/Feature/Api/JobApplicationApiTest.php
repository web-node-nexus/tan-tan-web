<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobApplicationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_include_jobs_and_local_services(): void
    {
        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'jobs', 'kind' => 'job'])
            ->assertJsonFragment(['slug' => 'job-sales', 'kind' => 'job'])
            ->assertJsonFragment(['slug' => 'services', 'kind' => 'service'])
            ->assertJsonFragment(['slug' => 'service-plumber', 'kind' => 'service']);
    }

    public function test_parent_category_filter_includes_child_listings(): void
    {
        $jobs = Category::query()->where('slug', 'jobs')->firstOrFail();
        $sales = Category::query()->where('slug', 'job-sales')->firstOrFail();
        $employer = User::factory()->create();

        Listing::query()->create([
            'user_id' => $employer->id,
            'category_id' => $sales->id,
            'title' => 'Store sales associate',
            'slug' => 'store-sales-associate',
            'description' => 'Full time sales role at a neighbourhood store. Incentive after the first month.',
            'price' => 16000,
            'condition' => 'new',
            'status' => 'active',
            'published_at' => now(),
        ]);

        $this->getJson('/api/v1/listings?category_id='.$jobs->id)
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Store sales associate')
            ->assertJsonPath('data.0.category.kind', 'job');
    }

    public function test_candidate_can_apply_and_employer_can_shortlist(): void
    {
        $sales = Category::query()->where('slug', 'job-sales')->firstOrFail();
        $employer = User::factory()->create();
        $candidate = User::factory()->create();
        $listing = Listing::query()->create([
            'user_id' => $employer->id,
            'category_id' => $sales->id,
            'title' => 'Marketing intern',
            'slug' => 'marketing-intern',
            'description' => 'Three month marketing internship. Social posts and local shop visits. Stipend paid monthly.',
            'price' => 12000,
            'condition' => 'new',
            'status' => 'active',
            'published_at' => now(),
        ]);

        Sanctum::actingAs($candidate);

        $created = $this->postJson('/api/v1/listings/'.$listing->id.'/applications', [
            'note' => 'I have one year of shop sales experience in Lucknow.',
            'experience' => '1_2',
            'education' => 'twelfth',
            'can_join' => 'immediate',
            'expected_salary' => 14000,
        ])->assertCreated()
            ->assertJsonPath('data.application.status', 'applied')
            ->assertJsonPath('data.application.experience', '1_2')
            ->assertJsonPath('data.application.expected_salary', 14000);

        $this->assertNotNull($created->json('data.conversation_id'));

        $this->postJson('/api/v1/listings/'.$listing->id.'/applications', [
            'note' => 'Applying again with the same profile details.',
            'experience' => '1_2',
            'education' => 'twelfth',
            'can_join' => 'immediate',
            'expected_salary' => 14000,
        ])->assertStatus(409);

        Sanctum::actingAs($employer);

        $this->getJson('/api/v1/listings/'.$listing->id.'/applications')
            ->assertOk()
            ->assertJsonPath('data.0.applicant.id', $candidate->id);

        $applicationId = $created->json('data.application.id');

        $this->patchJson('/api/v1/applications/'.$applicationId, ['status' => 'shortlisted'])
            ->assertOk()
            ->assertJsonPath('data.status', 'shortlisted');

        $this->patchJson('/api/v1/applications/'.$applicationId, ['status' => 'hired'])
            ->assertOk()
            ->assertJsonPath('data.status', 'hired');

        Sanctum::actingAs($candidate);
        $this->getJson('/api/v1/me/applications')
            ->assertOk()
            ->assertJsonPath('data.0.listing.title', 'Marketing intern')
            ->assertJsonPath('data.0.status', 'hired');
    }

    public function test_service_listing_rejects_job_application(): void
    {
        $plumber = Category::query()->where('slug', 'service-plumber')->firstOrFail();
        $provider = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::query()->create([
            'user_id' => $provider->id,
            'category_id' => $plumber->id,
            'title' => 'Plumber for bathroom leaks',
            'slug' => 'plumber-bathroom-leaks',
            'description' => 'Same-day plumbing for leaks, taps and blocked drains. Visit charge is the starting price.',
            'price' => 350,
            'condition' => 'new',
            'status' => 'active',
            'published_at' => now(),
        ]);

        Sanctum::actingAs($buyer);

        $this->postJson('/api/v1/listings/'.$listing->id.'/applications', [
            'note' => 'Please visit tomorrow morning for a kitchen leak.',
        ])->assertStatus(422);
    }
}
