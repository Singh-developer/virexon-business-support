<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AgentDocument;
use App\Models\Concerns\HandlesTrash;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HandlesTrash;

    protected $fillable = ['name', 'email', 'password', 'role_id', 'business_id', 'status', 'phone'];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Restored from old model
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
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

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function documents()
    {
        return $this->hasMany(AgentDocument::class);
    }

    public function sanctionLetters()
    {
        return $this->hasMany(SanctionLetter::class, 'user_id');
    }

    public function advances()
    {
        return $this->hasMany(Advance::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    // --- Relationships Restored From Old Model ---

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function bankDetails()
    {
        return $this->hasMany(BankDetail::class);
    }
    
    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function referencePersons()
    {
        return $this->hasMany(ReferencePerson::class);
    }

    // ---------------------------------------------

    /**
     * Related records that are trashed / restored / purged together with an agent.
     * Uploaded files on those records are untouched until a permanent delete.
     *
     * @return array<int, string>
     */
    protected function trashRelations(): array
    {
        return [
            'virtualCard',
            'sanctionLetters',
            'documents',
            'advances',
            'commissions',
            'payments',
            'tickets',
            'detail',
            'referencePersons',
        ];
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
