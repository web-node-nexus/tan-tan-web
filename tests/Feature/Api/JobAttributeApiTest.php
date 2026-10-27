<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use App\Support\JobAttributes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobAttributeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_job_type_stores_its_own_fields(): void
    {
        foreach (JobAttributes::examples() as $slug => $attributes) {
            $category = Category::query()->where('slug', $slug)->firstOrFail();
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $created = $this->postJson('/api/v1/listings', [
                'title' => 'Hiring for '.$slug,
                'description' => 'A local opening with a clear salary, timings and the work expected each week.',
                'price' => 15000,
                'category_id' => $category->id,
                'condition' => 'new',
                'attributes' => $attributes,
            ])->assertCreated();

            foreach ($attributes as $key => $value) {
                $created->assertJsonPath('data.attributes.'.$key, $value);
            }

            $this->postJson('/api/v1/listings', [
                'title' => 'Incomplete job '.$slug,
                'description' => 'This description is long enough but the job fields are missing.',
                'price' => 15000,
                'category_id' => $category->id,
                'condition' => 'new',
            ])->assertStatus(422);
        }
    }
}
