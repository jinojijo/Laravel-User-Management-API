<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Role constants
    const ROLE_ADMIN = 1;
    const ROLE_SUPERVISOR = 2;
    const ROLE_AGENT = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'role' => 'integer',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the user's role name.
     *
     * @return string
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SUPERVISOR => 'Supervisor',
            self::ROLE_AGENT => 'Agent',
            default => 'Unknown',
        };
    }

    /**
     * Check if user is admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is supervisor.
     *
     * @return bool
     */
    public function isSupervisor()
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    /**
     * Check if user is agent.
     *
     * @return bool
     */
    public function isAgent()
    {
        return $this->role === self::ROLE_AGENT;
    }

    /**
     * Get valid roles array.
     *
     * @return array
     */
    public static function getValidRoles()
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_SUPERVISOR,
            self::ROLE_AGENT,
        ];
    }
}
