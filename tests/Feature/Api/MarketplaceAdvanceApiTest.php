<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MarketplaceAdvanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_jobs_by_work_mode_and_verified_sellers(): void
    {
        $fullTime = Category::query()->where('slug', 'job-full-time')->firstOrFail();
        $office = User::factory()->create(['is_verified' => true]);
        $remote = User::factory()->create(['is_verified' => false]);

        $this->job($office, $fullTime, 'office-role', 'office');
        $this->job($remote, $fullTime, 'remote-role', 'remote');

        $this->getJson('/api/v1/listings?kind=job&work_mode=office')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'office-role')
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/listings?kind=job&verified=1')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'office-role')
            ->assertJsonCount(1, 'data');
    }

    public function test_search_filters_services_that_visit_the_same_day(): void
    {
        $electrician = Category::query()->where('slug', 'service-electrician')->firstOrFail();
        $provider = User::factory()->create();

        Listing::query()->create([
            'user_id' => $provider->id,
            'category_id' => $electrician->id,
            'title' => 'Same day fan repair',
            'slug' => 'same-day-fan',
            'description' => 'Fan and switchboard repair with a same-day visit across the neighbourhood.',
            'price' => 299,
            'condition' => 'new',
            'status' => 'active',
            'attributes' => ['work_types' => ['fan'], 'same_day' => true, 'experience_years' => 4],
            'published_at' => now(),
        ]);
        Listing::query()->create([
            'user_id' => $provider->id,
            'category_id' => $electrician->id,
            'title' => 'Next week wiring',
            'slug' => 'next-week-wiring',
            'description' => 'Full house wiring booked for next week, not a same-day emergency visit.',
            'price' => 499,
            'condition' => 'new',
            'status' => 'active',
            'attributes' => ['work_types' => ['wiring'], 'same_day' => false, 'experience_years' => 8],
            'published_at' => now()->subMinute(),
        ]);

        $this->getJson('/api/v1/listings?kind=service&same_day=1')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'same-day-fan')
            ->assertJsonCount(1, 'data');
    }

    public function test_employer_schedules_interview_and_candidate_can_confirm_or_withdraw(): void
    {
        $sales = Category::query()->where('slug', 'job-sales')->firstOrFail();
        $employer = User::factory()->create();
        $candidate = User::factory()->create();
        $listing = $this->job($employer, $sales, 'sales-interview', 'office');

        Sanctum::actingAs($candidate);
        $created = $this->postJson('/api/v1/listings/'.$listing->id.'/applications', [
            'note' => 'I can start field sales in Lucknow this month.',
            'experience' => 'fresher',
            'education' => 'graduate',
            'can_join' => '15_days',
            'expected_salary' => 16000,
        ])->assertCreated();
        $applicationId = $created->json('data.application.id');

        Sanctum::actingAs($employer);
        $this->patchJson('/api/v1/applications/'.$applicationId, [
            'status' => 'interview',
            'interview_on' => now()->addDay()->toDateString(),
            'interview_slot' => 'morning',
            'interview_place' => 'Hazratganj store',
        ])->assertOk()
            ->assertJsonPath('data.status', 'interview')
            ->assertJsonPath('data.interview_place', 'Hazratganj store');

        $this->patchJson('/api/v1/applications/'.$applicationId, [
            'status' => 'interview',
        ])->assertStatus(422);

        Sanctum::actingAs($candidate);
        $this->postJson('/api/v1/applications/'.$applicationId.'/respond', ['action' => 'confirm'])
            ->assertOk()
            ->assertJsonPath('data.interview_reply', 'confirmed');

        $second = User::factory()->create();
        Sanctum::actingAs($second);
        $other = $this->postJson('/api/v1/listings/'.$listing->id.'/applications', [
            'note' => 'I have shop sales experience and can join soon.',
            'experience' => '1_2',
            'education' => 'twelfth',
            'can_join' => 'immediate',
            'expected_salary' => 15000,
        ])->assertCreated();

        $this->postJson('/api/v1/applications/'.$other->json('data.application.id').'/respond', ['action' => 'withdraw'])
            ->assertOk()
            ->assertJsonPath('data.status', 'withdrawn');
    }

    private function job(User $user, Category $category, string $slug, string $mode): Listing
    {
        return Listing::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Opening '.$slug,
            'slug' => $slug,
            'description' => 'A local opening with fixed timings, a clear salary and weekly off on Sunday.',
            'price' => 18000,
            'condition' => 'new',
            'status' => 'active',
            'attributes' => [
                'experience' => 'fresher',
                'work_mode' => $mode,
                'openings' => 1,
                'education' => 'any',
            ],
            'published_at' => now(),
        ]);
    }
}
