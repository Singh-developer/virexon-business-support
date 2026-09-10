<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name', 'slug', 'status', 'is_active', 'mode', 'environment',
        'credentials', 'configuration',
        'sandbox_key_id', 'sandbox_key_secret',
        'live_key_id', 'live_key_secret',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'array',
    ];
}
