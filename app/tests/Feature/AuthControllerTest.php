<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user registration with valid data.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => User::ROLE_AGENT,
            'email' => 'john@gmail.com',
            'password' => 'Password123!',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'date_of_birth' => '1990-01-01',
            'timezone' => 'America/New_York',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'first_name',
                            'last_name',
                            'full_name',
                            'role',
                            'role_name',
                            'email',
                            'location' => ['latitude', 'longitude'],
                            'date_of_birth',
                            'timezone',
                            'created_at',
                            'updated_at',
                        ],
                        'token',
                        'token_type'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@gmail.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => User::ROLE_AGENT,
        ]);
    }

    /**
     * Test user registration fails with invalid email.
     */
    public function test_registration_fails_with_invalid_email(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => User::ROLE_AGENT,
            'email' => 'invalid@email',
            'password' => 'Password123!',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'date_of_birth' => '1990-01-01',
            'timezone' => 'America/New_York',
        ];

        $response = $this->postJson('/api/auth/register', $userData);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user registration fails with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'john@gmail.com']);

        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'role' => User::ROLE_AGENT,
            'email' => 'john@gmail.com',
            'password' => 'Password123!',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'date_of_birth' => '1990-01-01',
            'timezone' => 'America/New_York',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@gmail.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@gmail.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'user',
                        'token',
                        'token_type'
                    ]
                ]);
    }

    /**
     * Test user login fails with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@gmail.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@gmail.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test authenticated user can logout.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Logout successful'
                ]);
    }

    /**
     * Test authenticated user can get their profile.
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'role',
                        'role_name',
                        'location',
                        'date_of_birth',
                        'timezone',
                        'created_at',
                        'updated_at',
                    ]
                ]);
    }

    /**
     * Test unauthenticated user cannot access protected routes.
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}