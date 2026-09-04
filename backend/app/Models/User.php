<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'headline',
        'phone',
        'target_salary_min',
        'target_salary_max',
        'preferred_location',
        'linkedin_id',
        'avatar',
        'google_id',
        'google_email',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'last_gmail_synced_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_access_token',
        'google_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'target_salary_min' => 'float',
            'target_salary_max' => 'float',
            'google_token_expires_at' => 'datetime',
            'last_gmail_synced_at' => 'datetime',
        ];
    }

    /**
     * Check if user has connected their Google / Gmail account.
     */
    public function hasGoogleConnected(): bool
    {
        return !empty($this->google_access_token) || !empty($this->google_refresh_token);
    }

    /**
     * Check if user has admin privileges.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * User's job applications.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
