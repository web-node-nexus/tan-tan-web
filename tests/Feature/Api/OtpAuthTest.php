<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OtpAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'otp.driver' => 'twofactor',
            'otp.twofactor.api_key' => 'test-key',
            'otp.twofactor.template' => 'OTP1',
            'otp.twofactor.base_url' => 'https://2factor.in/API/V1',
        ]);
    }

    public function test_send_otp_calls_twofactor_autogen(): void
    {
        Http::fake([
            '2factor.in/*' => Http::response([
                'Status' => 'Success',
                'Details' => 'session-abc',
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/auth/otp/send', [
            'phone' => '9876543210',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phone', '9876543210')
            ->assertJsonPath('data.masked', '98******10');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/SMS/9876543210/AUTOGEN/OTP1');
        });
    }

    public function test_verify_otp_issues_sanctum_token(): void
    {
        Http::fake([
            '2factor.in/*/SMS/*/AUTOGEN/*' => Http::response([
                'Status' => 'Success',
                'Details' => 'session-abc',
            ], 200),
            '2factor.in/*/SMS/VERIFY/*' => Http::response([
                'Status' => 'Success',
                'Details' => 'OTP Matched',
            ], 200),
        ]);

        $this->postJson('/api/v1/auth/otp/send', ['phone' => '9876543210'])->assertOk();

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => '9876543210',
            'otp' => '123456',
            'name' => 'Riya',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_new_user', true)
            ->assertJsonPath('data.user.phone', '9876543210')
            ->assertJsonPath('data.user.name', 'Riya')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);

        $this->assertDatabaseHas('users', ['phone' => '9876543210']);
        $this->assertSame(1, User::query()->count());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/SMS/VERIFY/session-abc/123456');
        });
    }

    public function test_log_driver_accepts_dev_code(): void
    {
        config(['otp.driver' => 'log', 'otp.dev_code' => '123456']);

        $this->postJson('/api/v1/auth/otp/send', ['phone' => '9123456789'])->assertOk();

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => '9123456789',
            'otp' => '123456',
        ]);

        $response->assertOk()->assertJsonPath('data.user.phone', '9123456789');
    }
}
