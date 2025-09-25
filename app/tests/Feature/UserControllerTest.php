<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test authenticated user can list all users.
     */
    public function test_authenticated_user_can_list_users(): void
    {
        $user = User::factory()->create();
        User::factory()->count(5)->create();
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'first_name',
                            'last_name',
                            'full_name',
                            'role',
                            'role_name',
                            'email',
                            'location',
                            'date_of_birth',
                            'timezone',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'pagination'
                ]);
    }

    /**
     * Test authenticated user can create a new user.
     */
    public function test_authenticated_user_can_create_user(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Sanctum::actingAs($user);

        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => User::ROLE_SUPERVISOR,
            'email' => 'jane@gmail.com',
            'password' => 'Password123!',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'date_of_birth' => '1985-05-15',
            'timezone' => 'America/Los_Angeles',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'full_name',
                        'role',
                        'role_name',
                        'email',
                        'location',
                        'date_of_birth',
                        'timezone',
                        'created_at',
                        'updated_at',
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@gmail.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => User::ROLE_SUPERVISOR,
        ]);
    }

    /**
     * Test user creation fails with invalid data.
     */
    public function test_user_creation_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $userData = [
            'first_name' => '', // Invalid: empty
            'last_name' => 'Smith',
            'role' => 999, // Invalid: not in enum
            'email' => 'invalid@email', // Invalid: not a valid email
            'password' => '123', // Invalid: too short
            'latitude' => 200, // Invalid: out of range
            'longitude' => -200, // Invalid: out of range
            'date_of_birth' => '2026-01-01', // Invalid: future date
            'timezone' => 'Invalid/Timezone', // Invalid: not a valid timezone
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'first_name',
                    'role',
                    'email',
                    'password',
                    'latitude',
                    'longitude',
                    'date_of_birth',
                    'timezone'
                ]);
    }

    /**
     * Test authenticated user can view a specific user.
     */
    public function test_authenticated_user_can_view_specific_user(): void
    {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();
        
        Sanctum::actingAs($authUser);

        $response = $this->getJson("/api/users/{$targetUser->id}");

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
                ])
                ->assertJson([
                    'data' => [
                        'id' => $targetUser->id,
                        'email' => $targetUser->email,
                    ]
                ]);
    }

    /**
     * Test authenticated user can update a user.
     */
    public function test_authenticated_user_can_update_user(): void
    {
        $authUser = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $targetUser = User::factory()->create();
        
        Sanctum::actingAs($authUser);

        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'role' => User::ROLE_SUPERVISOR,
        ];

        $response = $this->putJson("/api/users/{$targetUser->id}", $updateData);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data'
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'role' => User::ROLE_SUPERVISOR,
        ]);
    }

    /**
     * Test user update fails with invalid email uniqueness.
     */
    public function test_user_update_fails_with_duplicate_email(): void
    {
        $authUser = User::factory()->create();
        $user1 = User::factory()->create(['email' => 'existing@gmail.com']);
        $user2 = User::factory()->create(['email' => 'user2@gmail.com']);
        
        Sanctum::actingAs($authUser);

        $updateData = [
            'email' => 'existing@gmail.com', // This email already exists
        ];

        $response = $this->putJson("/api/users/{$user2->id}", $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test authenticated user can delete a user.
     */
    public function test_authenticated_user_can_delete_user(): void
    {
        $authUser = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $targetUser = User::factory()->create();
        
        Sanctum::actingAs($authUser);

        $response = $this->deleteJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'User deleted successfully'
                ]);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    /**
     * Test unauthenticated user cannot access user endpoints.
     */
    public function test_unauthenticated_user_cannot_access_user_endpoints(): void
    {
        $user = User::factory()->create();

        // Test listing users
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        // Test creating user
        $response = $this->postJson('/api/users', []);
        $response->assertStatus(401);

        // Test viewing specific user
        $response = $this->getJson("/api/users/{$user->id}");
        $response->assertStatus(401);

        // Test updating user
        $response = $this->putJson("/api/users/{$user->id}", []);
        $response->assertStatus(401);

        // Test deleting user
        $response = $this->deleteJson("/api/users/{$user->id}");
        $response->assertStatus(401);
    }

    /**
     * Test user listing with search functionality.
     */
    public function test_user_listing_with_search(): void
    {
        $authUser = User::factory()->create();
        User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
        User::factory()->create(['first_name' => 'Bob', 'last_name' => 'Johnson']);
        
        Sanctum::actingAs($authUser);

        $response = $this->getJson('/api/users?search=John');

        $response->assertStatus(200);
        
        $responseData = $response->json();
        
        $this->assertCount(2, $responseData['data']); // Should find 'John' and 'Johnson'
    }

    /**
     * Test user listing with role filtering.
     */
    public function test_user_listing_with_role_filter(): void
    {
        $authUser = User::factory()->create();
        User::factory()->count(2)->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(3)->create(['role' => User::ROLE_SUPERVISOR]);
        User::factory()->count(4)->create(['role' => User::ROLE_AGENT]);
        
        Sanctum::actingAs($authUser);

        $response = $this->getJson('/api/users?role=' . User::ROLE_ADMIN);

        $response->assertStatus(200);

        // Assert that all returned users have role = Admin
        foreach ($response->json('data') as $user) {
            $this->assertEquals(User::ROLE_ADMIN, $user['role']);
        }

        // Assert pagination metadata
        $response->assertJson([
            'pagination' => [
                'total' => 2,
                'current_page' => 1,
                'per_page' => 15,
                'last_page' => 1,
                'from' => 1,
                'to' => 2,
            ]
        ]);
    }

    /**
     * Test user listing with pagination.
     */
    public function test_user_listing_with_pagination(): void
    {
        $authUser = User::factory()->create();
        User::factory()->count(25)->create(); // Create 25 additional users
        
        Sanctum::actingAs($authUser);

        $response = $this->getJson('/api/users?per_page=10');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data',
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to'
                    ]
                ]);

        $responseData = $response->json();
        $this->assertEquals(10, $responseData['pagination']['per_page']);
        $this->assertEquals(26, $responseData['pagination']['total']); // 25 + 1 auth user
        $this->assertCount(10, $responseData['data']);
    }
}