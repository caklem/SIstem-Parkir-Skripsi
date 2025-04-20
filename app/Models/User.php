<?php


namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the user's roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    /**
     * Check if user has a role
     */
    public function hasRole($roleName)
    {
        return $this->roles->where('name', $roleName)->count() > 0;
    }
    
    /**
     * Get user's role (singular, for compatibility)
     */
    public function role()
    {
        return $this->roles()->first();
    }
    
    /**
     * Check if user is management
     */
    public function isManagement()
    {
        return $this->hasRole('management');
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }
}