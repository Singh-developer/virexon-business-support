<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HandlesTrash;

class UserDetail extends Model
{
    use SoftDeletes, HandlesTrash;

    protected $guarded = []; // Allows mass assignment for all fields

    /**
     * Get the formatted max limit with currency symbol
     */
    public function getFormattedMaxLimitAttribute(): string
    {
        return '₹' . number_format($this->max_limit ?? 500000, 2);
    }

    /**
     * Get the commission display string
     */
    public function getCommissionDisplayAttribute(): string
    {
        if ($this->commission_type === 'fixed') {
            return '₹' . number_format($this->commission_fixed ?? 0, 2) . ' (Fixed)';
        }
        
        return number_format($this->commission_rate ?? 0, 4) . '%';
    }

    /**
     * Calculate commission for a given amount
     */
    public function calculateCommission(float $amount): float
    {
        if ($this->commission_type === 'fixed') {
            return (float) ($this->commission_fixed ?? 0);
        }
        
        return round($amount * (($this->commission_rate ?? 0) / 100), 2);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** KYC files uploaded during registration stay until permanent delete. */
    protected function trashFileMap(): array
    {
        return ['public' => ['pan_file_path', 'aadhar_file_path']];
    }
}