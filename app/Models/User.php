<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Get all requests made by this user.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Get pending requests by this user.
     */
    public function pendingRequests(): HasMany
    {
        return $this->hasMany(Request::class)->where('status', 'pending');
    }

    /**
     * Get active requests by this user.
     */
    public function activeRequests(): HasMany
    {
        return $this->hasMany(Request::class)->whereIn('status', ['approved', 'in_use']);
    }

    /**
     * Get requests approved by this admin.
     */
    public function approvedRequests(): HasMany
    {
        return $this->hasMany(Request::class, 'approved_by');
    }

    /**
     * Check if user has any overdue requests.
     */
    public function hasOverdueRequests(): bool
    {
        return $this->requests()
            ->where('status', 'in_use')
            ->where('end_date', '<', now())
            ->exists();
    }
}
