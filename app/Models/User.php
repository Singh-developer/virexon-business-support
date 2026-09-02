<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role_id', 'business_id', 'status', 'phone'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    /* public function business()
    {
        return $this->belongsTo(Business::class);
    } 
     public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function virtualCard()
    {
        return $this->hasOne(VirtualCard::class, 'agent_id');
    }
    public function isAgent(): bool
    {
        return $this->role?->slug === 'agent';
    }
    public function isAdmin(): bool
    {
        return in_array($this->role?->slug, ['admin', 'super-admin'], true);
    } */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function virtualCard()
    {
        return $this->hasOne(
            VirtualCard::class,
            'agent_id'
        );
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function hasPermission(string $permission): bool
    {
        return $this->role?->permissions()->where('name', $permission)->exists() ?? false;
    }

    public function isAgent(): bool
    {
        return $this->role?->slug === 'agent';
    }

    public function isAdmin(): bool
    {
        return in_array(
            $this->role?->slug,
            ['admin', 'super-admin'],
            true
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'super-admin';
    }
}
