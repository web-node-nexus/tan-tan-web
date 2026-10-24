<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_envelope(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'app',
                    'env',
                    'version',
                    'database',
                    'timezone',
                    'locale',
                ],
                'meta',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.version', 'v1');
    }

    public function test_health_respects_hindi_locale_header(): void
    {
        $response = $this->withHeader('Accept-Language', 'hi')->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonPath('data.locale', 'hi')
            ->assertJsonPath('message', 'टैन टैन API सही चल रहा है।');
    }
}
