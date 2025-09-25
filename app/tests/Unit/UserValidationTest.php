<?php

namespace Tests\Unit;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UserValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test store user request validation with valid data.
     */
    public function test_store_user_request_passes_with_valid_data(): void
    {
        $request = new StoreUserRequest();
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => User::ROLE_ADMIN,
            'email' => 'john@gmail.com',
            'password' => 'Password123!',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'date_of_birth' => '1990-01-01',
            'timezone' => 'America/New_York',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test store user request validation fails with invalid data.
     */
    public function test_store_user_request_fails_with_invalid_data(): void
    {
        $request = new StoreUserRequest();
        
        // Test missing required fields
        $data = [];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());

        // Test invalid email
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => User::ROLE_ADMIN,
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'date_of_birth' => '1990-01-01',
            'timezone' => 'America/New_York',
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());

        // Test invalid role
        $data['email'] = 'john@gmail.com';
        $data['role'] = 999;
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('role', $validator->errors()->toArray());

        // Test invalid latitude
        $data['role'] = User::ROLE_ADMIN;
        $data['latitude'] = 200; // Out of range
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('latitude', $validator->errors()->toArray());

        // Test invalid longitude
        $data['latitude'] = 40.7128;
        $data['longitude'] = -200; // Out of range
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('longitude', $validator->errors()->toArray());

        // Test invalid date of birth (future date)
        $data['longitude'] = -74.0060;
        $data['date_of_birth'] = '2030-01-01';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_of_birth', $validator->errors()->toArray());

        // Test weak password
        $data['date_of_birth'] = '1990-01-01';
        $data['password'] = '123';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * Test update user request validation with valid data.
     */
    public function test_update_user_request_passes_with_valid_data(): void
    {
        $user = User::factory()->create();
        
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () use ($user) {
            return (object) ['parameter' => function ($name) use ($user) {
                return $name === 'user' ? $user->id : null;
            }];
        });

        $data = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'role' => User::ROLE_SUPERVISOR,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test update user request validation with email uniqueness.
     */
    public function test_update_user_request_email_uniqueness(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@gmail.com']);
        $user2 = User::factory()->create(['email' => 'user2@gmail.com']);
        
        $request = new UpdateUserRequest();
        $request->setRouteResolver(function () use ($user2) {
            return (object) ['parameter' => function ($name) use ($user2) {
                return $name === 'user' ? $user2->id : null;
            }];
        });

        // Test updating to existing email (should fail)
        $data = ['email' => 'user1@gmail.com'];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());

        // Test updating to same email (should pass)
        $data = ['email' => 'user2@gmail.com'];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());

        // Test updating to new email (should pass)
        $data = ['email' => 'new@gmail.com'];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /**
     * Test name validation regex.
     */
    public function test_name_validation_regex(): void
    {
        $request = new StoreUserRequest();
        
        // Valid names
        $validNames = [
            'John',
            'Mary-Jane',
            "O'Connor",
            'Jean-Luc',
            'José',
            'Anne Marie',
            'Dr. Smith',
        ];

        foreach ($validNames as $name) {
            $data = [
                'first_name' => $name,
                'last_name' => 'Doe',
                'role' => User::ROLE_ADMIN,
                'email' => 'test@gmail.com',
                'password' => 'Password123!',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'date_of_birth' => '1990-01-01',
                'timezone' => 'America/New_York',
            ];
            
            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->passes(), "Valid name '{$name}' should pass validation");
        }

        // Invalid names
        $invalidNames = [
            'John123',
            'John@Doe',
            'John#',
            'John$',
        ];

        foreach ($invalidNames as $name) {
            $data = [
                'first_name' => $name,
                'last_name' => 'Doe',
                'role' => User::ROLE_ADMIN,
                'email' => 'test@gmail.com',
                'password' => 'Password123!',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'date_of_birth' => '1990-01-01',
                'timezone' => 'America/New_York',
            ];
            
            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->fails(), "Invalid name '{$name}' should fail validation");
            $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
        }
    }

    /**
     * Test password strength validation.
     */
    public function test_password_strength_validation(): void
    {
        $request = new StoreUserRequest();
        
        // Valid passwords
        $validPasswords = [
            'Password123!',
            'MyStr0ng@Pass',
            'Complex1$',
            'Secure#2023',
        ];

        foreach ($validPasswords as $password) {
            $data = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'role' => User::ROLE_ADMIN,
                'email' => 'test@gmail.com',
                'password' => $password,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'date_of_birth' => '1990-01-01',
                'timezone' => 'America/New_York',
            ];
            
            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->passes(), "Valid password '{$password}' should pass validation");
        }

        // Invalid passwords
        $invalidPasswords = [
            'password', // No uppercase, no number, no symbol
            'PASSWORD', // No lowercase, no number, no symbol
            'Password', // No number, no symbol
            'Password123', // No symbol
            'Password!', // No number
            '123!@#', // No letters
            'Pass1!', // Too short
        ];

        foreach ($invalidPasswords as $password) {
            $data = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'role' => User::ROLE_ADMIN,
                'email' => 'test@gmail.com',
                'password' => $password,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'date_of_birth' => '1990-01-01',
                'timezone' => 'America/New_York',
            ];
            
            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->fails(), "Invalid password '{$password}' should fail validation");
            $this->assertArrayHasKey('password', $validator->errors()->toArray());
        }
    }
}