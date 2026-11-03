<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token_on_valid_credentials(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'api_test_user@cargotrace.com'],
            ['name' => 'API Test User', 'password' => Hash::make('secret123'), 'role' => 'operator']
        );

        $response = $this->postJson('/api/login', [
            'email' => 'api_test_user@cargotrace.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }

    public function test_api_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@cargotrace.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_api_request_is_blocked(): void
    {
        $response = $this->getJson('/api/shipments');
        $response->assertStatus(401);
    }
}
