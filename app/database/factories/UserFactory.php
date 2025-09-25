<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'role' => fake()->randomElement([User::ROLE_ADMIN, User::ROLE_SUPERVISOR, User::ROLE_AGENT]),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('Password123!'),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'timezone' => fake()->timezone(),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Indicate that the user is a supervisor.
     */
    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_SUPERVISOR,
        ]);
    }

    /**
     * Indicate that the user is an agent.
     */
    public function agent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_AGENT,
        ]);
    }

    /**
     * Indicate that the user has a specific location.
     */
    public function withLocation(float $latitude, float $longitude): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /**
     * Indicate that the user has a specific timezone.
     */
    public function withTimezone(string $timezone): static
    {
        return $this->state(fn (array $attributes) => [
            'timezone' => $timezone,
        ]);
    }
}
