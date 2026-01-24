<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
     * Convenience wrapper matching project conventions.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    public function permissionsList()
    {
        return $this->getAllPermissions();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasPermissionTo('system.all') || $this->hasRole('superadmin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isPublicUser(): bool
    {
        return $this->hasRole('public');
    }

    // LEGACY: previous User model without convenience helpers
    /*
    // original file contained only HasFactory, Notifiable, HasRoles traits
    // and default $fillable, $hidden, $casts only.
    */

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
}
