<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssertionLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'title',
        'subject',
        'greeting',
        'body',
        'dynamic_fields',
        'closing',
        'notes',
        'signature_name',
        'signature_designation',
        'signature_company',
        'signature_image',
        'pdf_path',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dynamic_fields' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    /**
     * Get a dynamic field value by key.
     */
    public function getDynamicValue(string $key, ?string $default = null): ?string
    {
        return $this->dynamic_fields[$key] ?? $default;
    }

    /**
     * Get the public URL of the signature image.
     */
    public function getSignatureImageUrlAttribute(): ?string
    {
        if (!$this->signature_image) {
            return null;
        }
        return asset('storage/' . $this->signature_image);
    }
}
