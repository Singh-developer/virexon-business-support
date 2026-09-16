<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advance extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'total_amount',
        'outstanding_amount',
        'repayment_type',
        'emi_amount',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
