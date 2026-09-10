<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'order_id', 'type', 'status',
        'gateway', 'txn_token', 'txn_id', 'bank_txn_id',
        'payment_mode', 'gateway_name', 'gateway_response', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
