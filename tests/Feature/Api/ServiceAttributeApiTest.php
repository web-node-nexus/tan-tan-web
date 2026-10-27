<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use App\Support\ServiceAttributes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceAttributeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_local_service_stores_its_own_fields(): void
    {
        foreach (ServiceAttributes::examples() as $slug => $attributes) {
            $category = Category::query()->where('slug', $slug)->firstOrFail();
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $created = $this->postJson('/api/v1/listings', [
                'title' => 'Local service '.$slug,
                'description' => 'Available in the neighbourhood with a clear starting price and honest scope.',
                'price' => 499,
                'category_id' => $category->id,
                'condition' => 'new',
                'attributes' => $attributes,
            ])->assertCreated();

            foreach ($attributes as $key => $value) {
                $created->assertJsonPath('data.attributes.'.$key, $value);
            }

            $this->postJson('/api/v1/listings', [
                'title' => 'Incomplete service '.$slug,
                'description' => 'This description is long enough but the service fields are missing.',
                'price' => 499,
                'category_id' => $category->id,
                'condition' => 'new',
            ])->assertStatus(422);
        }
    }
}
