<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;

class Payment extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'card_id',
        'payment_type',
        'gateway',
        'amount',
        'currency',
        'reference',
        'gateway_payment_id',
        'gateway_order_id',
        'status',
        'gateway_response',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function card()
    {
        return $this->belongsTo(VirtualCard::class, 'card_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function isRepayment(): bool
    {
        return $this->payment_type === 'repayment';
    }

    public function isSpending(): bool
    {
        return $this->payment_type === 'spending';
    }
}
