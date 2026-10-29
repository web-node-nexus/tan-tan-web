<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrustApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_scam_copy_is_rejected_before_the_ad_is_created(): void
    {
        $category = Category::query()->create([
            'slug' => 'books',
            'name_en' => 'Books',
            'name_hi' => 'किताबें',
            'kind' => 'product',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/listings', [
            'title' => 'Sofa set for pickup',
            'description' => 'Pay the registration fee before I share the address with you today.',
            'price' => 8000,
            'category_id' => $category->id,
            'condition' => 'used',
        ])->assertStatus(422)
            ->assertJsonPath('errors.description.0', __('api.trust.fraud_blocked'));

        $this->assertDatabaseCount('listings', 0);
    }

    public function test_kyc_stays_pending_until_review_and_blocks_a_reused_id(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->post('/api/v1/me/kyc', [
            'legal_name' => 'Asha Verma',
            'document_type' => 'pan',
            'document_number' => 'ABCDE1234F',
            'document' => UploadedFile::fake()->image('pan.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertCreated()
            ->assertJsonPath('data.kyc_status', 'pending')
            ->assertJsonPath('data.document_last4', '234F');

        $this->assertFalse($user->fresh()->is_verified);

        Sanctum::actingAs(User::factory()->create());
        $this->post('/api/v1/me/kyc', [
            'legal_name' => 'Other Person',
            'document_type' => 'pan',
            'document_number' => 'ABCDE1234F',
            'document' => UploadedFile::fake()->image('pan.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertStatus(422);

        $this->assertSame(1, KycSubmission::query()->count());
    }

    public function test_business_account_is_visible_on_the_seller_profile(): void
    {
        $user = User::factory()->create(['name' => 'Ravi']);
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/me/business', [
            'account_type' => 'business',
            'business_name' => 'Ravi Motors',
            'gstin' => '27ABCDE1234F1Z5',
        ])->assertOk()
            ->assertJsonPath('data.account_type', 'business')
            ->assertJsonPath('data.business_name', 'Ravi Motors');

        $this->getJson('/api/v1/sellers/'.$user->id)
            ->assertOk()
            ->assertJsonPath('data.business_name', 'Ravi Motors')
            ->assertJsonPath('data.account_type', 'business');
    }
}
