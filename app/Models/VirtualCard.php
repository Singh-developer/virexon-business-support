<?php

namespace App\Models;

use App\Enums\CardStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class VirtualCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'agent_id',
        'reference',
        'encrypted_pan',
        'last4',
        'provider_card_id',
        'cardholder_name',
        'status',
        'card_limit',
        'daily_limit',
        'monthly_limit',
        'per_transaction_limit',
        'current_usage',
        'expiry_date',
        'created_by',
        'last_transaction_at',
    ];

    protected $casts = [
        'status' => CardStatus::class,
        'expiry_date' => 'date',
        'last_transaction_at' => 'datetime',
        'card_limit' => 'decimal:2',
        'daily_limit' => 'decimal:2',
        'monthly_limit' => 'decimal:2',
        'per_transaction_limit' => 'decimal:2',
        'current_usage' => 'decimal:2',
    ];

    protected $hidden = [
        'encrypted_pan',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'card_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'card_id');
    }

    public function getRemainingLimitAttribute(): float
    {
        return max(
            0,
            (float) $this->card_limit - (float) $this->current_usage
        );
    }

    /**
     * Return the decrypted PAN only when the caller
     * has already passed the authorization check.
     */
    public function revealPan(): string
    {
        if (! $this->encrypted_pan) {
            throw new RuntimeException('Card number is not available.');
        }

        return decrypt($this->encrypted_pan);
    }

    public function maskedPan(): string
    {
        if (! $this->last4) {
            return '•••• •••• •••• ••••';
        }

        return '•••• •••• •••• ' . $this->last4;
    }

    public function formattedPan(): ?string
    {
        $pan = $this->revealPan();

        return implode(
            ' ',
            str_split($pan, 4)
        );
    }
}
