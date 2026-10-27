<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Support\BookingDetails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceBookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_book_a_visit_and_provider_can_accept(): void
    {
        $plumber = Category::query()->where('slug', 'service-plumber')->firstOrFail();
        $provider = User::factory()->create();
        $customer = User::factory()->create();
        $listing = Listing::query()->create([
            'user_id' => $provider->id,
            'category_id' => $plumber->id,
            'title' => 'Same day plumber',
            'slug' => 'same-day-plumber',
            'description' => 'Leak repair and tap fitting across the neighbourhood. Visit charge is the starting price.',
            'price' => 349,
            'condition' => 'new',
            'status' => 'active',
            'published_at' => now(),
        ]);

        Sanctum::actingAs($customer);
        $visit = now()->addDay()->toDateString();

        $created = $this->postJson('/api/v1/listings/'.$listing->id.'/bookings', [
            'visit_on' => $visit,
            'slot' => 'morning',
            'note' => 'Kitchen sink is leaking since last night.',
            'details' => ['issue' => 'leak', 'water_off' => true],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'requested')
            ->assertJsonPath('data.slot', 'morning')
            ->assertJsonPath('data.details.issue', 'leak');

        $this->postJson('/api/v1/listings/'.$listing->id.'/bookings', [
            'visit_on' => $visit,
            'slot' => 'morning',
            'note' => 'Booking the same morning slot again.',
            'details' => ['issue' => 'leak', 'water_off' => true],
        ])->assertStatus(409);

        $this->getJson('/api/v1/me/bookings')
            ->assertOk()
            ->assertJsonPath('data.0.listing.title', 'Same day plumber');

        Sanctum::actingAs($provider);
        $this->patchJson('/api/v1/bookings/'.$created->json('data.id'), ['status' => 'accepted'])
            ->assertStatus(422);

        $this->patchJson('/api/v1/bookings/'.$created->json('data.id'), [
            'status' => 'accepted',
            'quoted_amount' => 450,
        ])->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.quoted_amount', 450);
    }

    public function test_job_listing_rejects_a_service_booking(): void
    {
        $sales = Category::query()->where('slug', 'job-sales')->firstOrFail();
        $employer = User::factory()->create();
        $candidate = User::factory()->create();
        $listing = Listing::query()->create([
            'user_id' => $employer->id,
            'category_id' => $sales->id,
            'title' => 'Shop sales role',
            'slug' => 'shop-sales-role',
            'description' => 'Full time sales role with a monthly salary and no registration fee at all.',
            'price' => 15000,
            'condition' => 'new',
            'status' => 'active',
            'published_at' => now(),
        ]);

        Sanctum::actingAs($candidate);
        $this->postJson('/api/v1/listings/'.$listing->id.'/bookings', [
            'visit_on' => now()->addDay()->toDateString(),
            'slot' => 'evening',
            'note' => 'I want to visit the shop for an interview.',
        ])->assertStatus(422);
    }

    public function test_each_service_booking_stores_its_own_details_and_can_be_cancelled(): void
    {
        foreach (BookingDetails::examples() as $slug => $details) {
            $category = Category::query()->where('slug', $slug)->firstOrFail();
            $provider = User::factory()->create();
            $customer = User::factory()->create();
            $listing = Listing::query()->create([
                'user_id' => $provider->id,
                'category_id' => $category->id,
                'title' => 'Visit for '.$slug,
                'slug' => 'visit-'.$slug,
                'description' => 'A local visit with a clear starting price and the work spelled out before arrival.',
                'price' => 499,
                'condition' => 'new',
                'status' => 'active',
                'published_at' => now(),
            ]);

            Sanctum::actingAs($customer);
            $this->postJson('/api/v1/listings/'.$listing->id.'/bookings', [
                'visit_on' => now()->addDay()->toDateString(),
                'slot' => 'afternoon',
                'note' => 'Please come prepared for this exact job.',
            ])->assertStatus(422);

            $created = $this->postJson('/api/v1/listings/'.$listing->id.'/bookings', [
                'visit_on' => now()->addDay()->toDateString(),
                'slot' => 'afternoon',
                'note' => 'Please come prepared for this exact job.',
                'details' => $details,
            ])->assertCreated();

            foreach ($details as $key => $value) {
                $created->assertJsonPath('data.details.'.$key, $value);
            }

            $this->postJson('/api/v1/bookings/'.$created->json('data.id').'/cancel')
                ->assertOk()
                ->assertJsonPath('data.status', 'cancelled');
        }
    }
}
