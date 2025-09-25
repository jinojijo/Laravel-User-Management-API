<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user creation with valid data.
     */
    public function test_user_creation_with_valid_data(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => User::ROLE_ADMIN,
            'email' => 'john@gmail.com',
            'password' => 'password123',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'date_of_birth' => '1990-01-01',
            'timezone' => 'America/New_York',
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals(User::ROLE_ADMIN, $user->role);
        $this->assertEquals('john@gmail.com', $user->email);
        $this->assertEquals(40.7128, $user->latitude);
        $this->assertEquals(-74.0060, $user->longitude);
        $this->assertTrue($user->exists);
    }

    /**
     * Test user full name attribute.
     */
    public function test_user_full_name_attribute(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    /**
     * Test user role name attribute.
     */
    public function test_user_role_name_attribute(): void
    {
        $adminUser = User::factory()->make(['role' => User::ROLE_ADMIN]);
        $supervisorUser = User::factory()->make(['role' => User::ROLE_SUPERVISOR]);
        $agentUser = User::factory()->make(['role' => User::ROLE_AGENT]);

        $this->assertEquals('Admin', $adminUser->role_name);
        $this->assertEquals('Supervisor', $supervisorUser->role_name);
        $this->assertEquals('Agent', $agentUser->role_name);
    }

    /**
     * Test user role checking methods.
     */
    public function test_user_role_checking_methods(): void
    {
        $adminUser = User::factory()->make(['role' => User::ROLE_ADMIN]);
        $supervisorUser = User::factory()->make(['role' => User::ROLE_SUPERVISOR]);
        $agentUser = User::factory()->make(['role' => User::ROLE_AGENT]);

        // Test admin user
        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($adminUser->isSupervisor());
        $this->assertFalse($adminUser->isAgent());

        // Test supervisor user
        $this->assertFalse($supervisorUser->isAdmin());
        $this->assertTrue($supervisorUser->isSupervisor());
        $this->assertFalse($supervisorUser->isAgent());

        // Test agent user
        $this->assertFalse($agentUser->isAdmin());
        $this->assertFalse($agentUser->isSupervisor());
        $this->assertTrue($agentUser->isAgent());
    }

    /**
     * Test valid roles method.
     */
    public function test_get_valid_roles_method(): void
    {
        $validRoles = User::getValidRoles();

        $this->assertIsArray($validRoles);
        $this->assertContains(User::ROLE_ADMIN, $validRoles);
        $this->assertContains(User::ROLE_SUPERVISOR, $validRoles);
        $this->assertContains(User::ROLE_AGENT, $validRoles);
        $this->assertCount(3, $validRoles);
    }

    /**
     * Test password is hidden from array conversion.
     */
    public function test_password_is_hidden_from_array(): void
    {
        $user = User::factory()->create([
            'password' => 'secret-password'
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
    }

    /**
     * Test date casting.
     */
    public function test_date_casting(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => '1990-01-01'
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->date_of_birth);
        $this->assertEquals('1990-01-01', $user->date_of_birth->format('Y-m-d'));
    }

    /**
     * Test location coordinates casting.
     */
    public function test_location_coordinates_casting(): void
    {
        $user = User::factory()->create([
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ]);

        $this->assertIsFloat($user->latitude);
        $this->assertIsFloat($user->longitude);
        $this->assertEquals(40.7128, $user->latitude);
        $this->assertEquals(-74.0060, $user->longitude);
    }

    /**
     * Test role constants are properly defined.
     */
    public function test_role_constants(): void
    {
        $this->assertEquals(1, User::ROLE_ADMIN);
        $this->assertEquals(2, User::ROLE_SUPERVISOR);
        $this->assertEquals(3, User::ROLE_AGENT);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $expectedFillable = [
            'first_name',
            'last_name',
            'role',
            'email',
            'password',
            'latitude',
            'longitude',
            'date_of_birth',
            'timezone',
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    /**
     * Test hidden attributes.
     */
    public function test_hidden_attributes(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
    }
}