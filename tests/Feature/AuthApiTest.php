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
            ['email' => 'api_test_user@cargopharma.com'],
            ['name' => 'API Test User', 'password' => Hash::make('secret123'), 'role' => 'operator']
        );

        $response = $this->postJson('/api/login', [
            'email' => 'api_test_user@cargopharma.com',
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
            'email' => 'nonexistent@cargopharma.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_api_request_is_blocked(): void
    {
        $response = $this->getJson('/api/shipments');
        $response->assertStatus(401);
    }

    public function test_public_registration_forces_customer_role(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Attacker Trying Admin',
            'email' => 'attacker@example.com',
            'password' => 'supersecret123',
            'role' => 'admin', // Malicious privilege escalation attempt
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'attacker@example.com',
            'role' => 'customer', // Must be customer, not admin
        ]);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@cargopharma.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inactive@cargopharma.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }
}
