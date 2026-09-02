<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $guarded = []; // Allows mass assignment for all fields

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}